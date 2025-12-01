<?php
class BorrowingModel
{
    private $conn;
    private $table = "borrowings";
    private $junctionTable = "borrowing_equipment";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ========================================================================
    // CREATE OPERATIONS
    // ========================================================================
    public function createEquipmentBorrowing($data, $equipmentItems)
    {
        try {
            $this->conn->beginTransaction();

            // Insert main borrowing record
            $query = "INSERT INTO $this->table 
                      (borrower_name, borrower_email, occupation, type, borrow_date, return_date, status, approval_status, notes)
                      VALUES (:borrower_name, :borrower_email, :occupation, 'equipment', :borrow_date, :return_date, :status, :approval_status, :notes)
                      RETURNING id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':borrower_name', $data['borrower_name']);
            $stmt->bindParam(':borrower_email', $data['borrower_email']);
            $stmt->bindParam(':occupation', $data['occupation']);
            $stmt->bindParam(':borrow_date', $data['borrow_date']);
            $stmt->bindParam(':return_date', $data['return_date']);
            
            $status = $data['status'] ?? 'active';
            $stmt->bindParam(':status', $status);
            
            $approvalStatus = $data['approval_status'] ?? 'pending';
            $stmt->bindParam(':approval_status', $approvalStatus);
            
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':notes', $notes);

            $stmt->execute();
            $borrowingId = $stmt->fetchColumn();

            if (!$borrowingId) {
                throw new Exception("Failed to create borrowing record");
            }

            // Insert equipment items into junction table
            $junctionQuery = "INSERT INTO $this->junctionTable 
                             (borrowing_id, equipment_id, quantity)
                             VALUES (:borrowing_id, :equipment_id, :quantity)";
            
            $junctionStmt = $this->conn->prepare($junctionQuery);

            foreach ($equipmentItems as $item) {
                // Validate equipment availability
                if (!$this->checkEquipmentAvailability($item['equipment_id'], $item['quantity'])) {
                    throw new Exception("Equipment ID {$item['equipment_id']} tidak tersedia dalam jumlah yang diminta");
                }

                $junctionStmt->bindParam(':borrowing_id', $borrowingId);
                $junctionStmt->bindParam(':equipment_id', $item['equipment_id']);
                $junctionStmt->bindParam(':quantity', $item['quantity']);
                $junctionStmt->execute();
            }

            $this->conn->commit();
            return $borrowingId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error creating equipment borrowing: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new room borrowing
     */
    public function createRoomBorrowing($data)
    {
        try {
            $this->conn->beginTransaction();

            // Check if Lab Inlet is already borrowed (active status)
            $checkQuery = "SELECT COUNT(*) FROM $this->table 
                          WHERE type = 'room' 
                          AND room_name = :room_name 
                          AND status = 'active'
                          AND (
                              (:borrow_date BETWEEN borrow_date AND return_date)
                              OR (:return_date BETWEEN borrow_date AND return_date)
                              OR (borrow_date BETWEEN :borrow_date AND :return_date)
                          )";
            
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':room_name', $data['room_name']);
            $checkStmt->bindParam(':borrow_date', $data['borrow_date']);
            $checkStmt->bindParam(':return_date', $data['return_date']);
            $checkStmt->execute();
            
            $count = $checkStmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception('Lab Inlet sudah dipinjam pada waktu tersebut. Silakan pilih waktu lain atau tunggu hingga peminjaman sebelumnya dikembalikan.');
            }

            $query = "INSERT INTO $this->table 
                      (borrower_name, borrower_email, occupation, type, room_name, borrow_date, return_date, status, approval_status, notes)
                      VALUES (:borrower_name, :borrower_email, :occupation, 'room', :room_name, :borrow_date, :return_date, :status, :approval_status, :notes)
                      RETURNING id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':borrower_name', $data['borrower_name']);
            $stmt->bindParam(':borrower_email', $data['borrower_email']);
            $stmt->bindParam(':occupation', $data['occupation']);
            $stmt->bindParam(':room_name', $data['room_name']);
            $stmt->bindParam(':borrow_date', $data['borrow_date']);
            $stmt->bindParam(':return_date', $data['return_date']);
            
            $status = $data['status'] ?? 'active';
            $stmt->bindParam(':status', $status);
            
            $approvalStatus = $data['approval_status'] ?? 'pending';
            $stmt->bindParam(':approval_status', $approvalStatus);
            
            $notes = $data['notes'] ?? null;
            $stmt->bindParam(':notes', $notes);

            $stmt->execute();
            $borrowingId = $stmt->fetchColumn();

            $this->conn->commit();
            return $borrowingId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error creating room borrowing: " . $e->getMessage());
            throw $e; // Re-throw to handle in calling code
        }
    }

    public function createBorrowing($data)
    {
        try {
            // Route based on type
            if ($data['type'] === 'room') {
                // Convert old structure to new structure
                $roomData = [
                    'borrower_name' => $data['borrower_name'],
                    'borrower_email' => $data['borrower_email'] ?? null,
                    'occupation' => $data['occupation'],
                    'room_name' => $data['item_name'], // item_name is room_name for room type
                    'borrow_date' => $data['borrow_date'],
                    'return_date' => $data['return_date'],
                    'status' => $data['status'] ?? 'active',
                    'approval_status' => $data['approval_status'] ?? 'pending',
                    'notes' => $data['notes'] ?? null
                ];
                
                return $this->createRoomBorrowing($roomData);
                
            } elseif ($data['type'] === 'equipment') {
                // Parse old item_name format (e.g., "IDS:1,2,3|Laptop, Mouse, Keyboard")
                $equipmentItems = [];
                
                if (!empty($data['item_id'])) {
                    // Single equipment (old format: item_id contains equipment_id)
                    $equipmentItems[] = [
                        'equipment_id' => $data['item_id'],
                        'quantity' => 1
                    ];
                } elseif (!empty($data['item_name'])) {
                    // Multiple equipment (old format: "IDS:1,2,3|Names" or just "1,2,3")
                    $itemName = $data['item_name'];
                    
                    if (strpos($itemName, 'IDS:') === 0) {
                        // Format: "IDS:1,2,3|Names"
                        $parts = explode('|', $itemName);
                        $idsString = str_replace('IDS:', '', $parts[0]);
                        $ids = explode(',', $idsString);
                        
                        foreach ($ids as $id) {
                            $id = trim($id);
                            if (is_numeric($id)) {
                                $equipmentItems[] = [
                                    'equipment_id' => intval($id),
                                    'quantity' => 1
                                ];
                            }
                        }
                    } elseif (strpos($itemName, ',') !== false) {
                        // Format: "1,2,3" (comma-separated IDs)
                        $ids = explode(',', $itemName);
                        foreach ($ids as $id) {
                            $id = trim($id);
                            if (is_numeric($id)) {
                                $equipmentItems[] = [
                                    'equipment_id' => intval($id),
                                    'quantity' => 1
                                ];
                            }
                        }
                    }
                }
                
                if (empty($equipmentItems)) {
                    throw new Exception("Invalid equipment data format");
                }
                
                $equipmentData = [
                    'borrower_name' => $data['borrower_name'],
                    'borrower_email' => $data['borrower_email'] ?? null,
                    'occupation' => $data['occupation'],
                    'borrow_date' => $data['borrow_date'],
                    'return_date' => $data['return_date'],
                    'status' => $data['status'] ?? 'active',
                    'approval_status' => $data['approval_status'] ?? 'pending',
                    'notes' => $data['notes'] ?? null
                ];
                
                return $this->createEquipmentBorrowing($equipmentData, $equipmentItems);
                
            } else {
                throw new Exception("Invalid borrowing type");
            }
            
        } catch (Exception $e) {
            error_log("Error in createBorrowing: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // READ OPERATIONS
    // ========================================================================

    /**
     * Get all borrowings using optimized view
     */
    public function getAllBorrowings($type = null, $status = null, $limit = 100, $offset = 0)
    {
        $query = "SELECT * FROM borrowing_details_view WHERE 1=1";

        if (!empty($type)) {
            $query .= " AND borrowing_type = :type";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
        }

        $query .= " ORDER BY borrowing_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
        }
        
        if (!empty($status)) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get borrowing history using optimized view
     * @param array $filters Filters for history query
     * @return array Borrowing history with calculated fields
     */
    public function getBorrowingHistory($filters = [])
    {
        $query = "SELECT * FROM borrowing_history_view WHERE 1=1";
        
        if (!empty($filters['type'])) {
            $query .= " AND type = :type";
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
        }
        
        if (!empty($filters['borrower_name'])) {
            $query .= " AND borrower_name ILIKE :borrower_name";
        }
        
        if (!empty($filters['from_date'])) {
            $query .= " AND borrow_date >= :from_date";
        }
        
        if (!empty($filters['to_date'])) {
            $query .= " AND return_date <= :to_date";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['type'])) {
            $stmt->bindParam(':type', $filters['type']);
        }
        
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        
        if (!empty($filters['borrower_name'])) {
            $searchTerm = '%' . $filters['borrower_name'] . '%';
            $stmt->bindParam(':borrower_name', $searchTerm);
        }
        
        if (!empty($filters['from_date'])) {
            $stmt->bindParam(':from_date', $filters['from_date']);
        }
        
        if (!empty($filters['to_date'])) {
            $stmt->bindParam(':to_date', $filters['to_date']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count borrowings
     */
    public function countBorrowings($type = null, $status = null)
    {
        $query = "SELECT COUNT(*) AS total FROM $this->table WHERE 1=1";

        if (!empty($type)) {
            $query .= " AND type = :type";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
        }
        
        if (!empty($status)) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get borrowing by ID with full details
     */
    public function getBorrowingById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM borrowing_details_view WHERE borrowing_id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If equipment borrowing, get detailed equipment list
        if ($result && $result['borrowing_type'] === 'equipment') {
            $equipmentQuery = "SELECT be.equipment_id, be.quantity, e.name, e.description
                              FROM $this->junctionTable be
                              JOIN equipment e ON be.equipment_id = e.id
                              WHERE be.borrowing_id = :id";
            $equipmentStmt = $this->conn->prepare($equipmentQuery);
            $equipmentStmt->bindParam(':id', $id);
            $equipmentStmt->execute();
            $result['equipment_items'] = $equipmentStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $result;
    }

    /**
     * Get equipment items for a borrowing
     */
    public function getBorrowingEquipment($borrowingId)
    {
        $query = "SELECT be.*, e.name, e.description
                  FROM $this->junctionTable be
                  JOIN equipment e ON be.equipment_id = e.id
                  WHERE be.borrowing_id = :borrowing_id
                  ORDER BY e.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':borrowing_id', $borrowingId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // UPDATE OPERATIONS
    // ========================================================================

    /**
     * Update borrowing status
     */
    public function updateStatus($id, $status)
    {
        $query = "UPDATE $this->table 
                  SET status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Update approval status (approved/rejected)
     */
    public function updateApprovalStatus($id, $approvalStatus, $adminNotes = null)
    {
        $query = "UPDATE $this->table 
                  SET approval_status = :approval_status, 
                      admin_notes = :admin_notes,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':approval_status', $approvalStatus);
        $stmt->bindParam(':admin_notes', $adminNotes);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Get borrowing data for email notification
     */
    public function getBorrowingForEmail($id)
    {
        $query = "SELECT b.*, 
                  CASE WHEN b.type = 'equipment' THEN 
                    (SELECT string_agg(e.name, ', ') 
                     FROM borrowing_equipment be 
                     JOIN equipment e ON be.equipment_id = e.id 
                     WHERE be.borrowing_id = b.id)
                  ELSE b.room_name 
                  END as item_names
                  FROM $this->table b 
                  WHERE b.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return borrowing (triggers will automatically restore equipment quantities)
     */
    public function returnBorrowing($id)
    {
        return $this->updateStatus($id, 'returned');
    }

    /**
     * Update borrowing details
     */
    public function updateBorrowing($id, $data)
    {
        try {
            $this->conn->beginTransaction();
            
            $borrowing = $this->getBorrowingById($id);
            if (!$borrowing) {
                throw new Exception("Borrowing not found");
            }

            // Update main borrowing record
            $query = "UPDATE $this->table SET 
                        borrower_name = :borrower_name,
                        occupation = :occupation,
                        borrow_date = :borrow_date,
                        return_date = :return_date,
                        status = :status,
                        notes = :notes,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':borrower_name', $data['borrower_name']);
            $stmt->bindParam(':occupation', $data['occupation']);
            $stmt->bindParam(':borrow_date', $data['borrow_date']);
            $stmt->bindParam(':return_date', $data['return_date']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Update room_id if room type
            if ($borrowing['borrowing_type'] === 'room' && isset($data['room_id'])) {
                $roomQuery = "UPDATE $this->table SET room_id = :room_id WHERE id = :id";
                $roomStmt = $this->conn->prepare($roomQuery);
                $roomStmt->bindParam(':room_id', $data['room_id']);
                $roomStmt->bindParam(':id', $id);
                $roomStmt->execute();
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error updating borrowing: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // DELETE OPERATIONS
    // ========================================================================

    /**
     * Delete borrowing (CASCADE will automatically delete junction table entries)
     */
    public function deleteBorrowing($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ========================================================================
    // VALIDATION & AVAILABILITY CHECKS (Using Database Functions)
    // ========================================================================

    /**
     * Check equipment availability using database function
     */
    public function checkEquipmentAvailability($equipmentId, $quantity = 1)
    {
        $stmt = $this->conn->prepare("SELECT check_equipment_availability(:equipment_id, :quantity)");
        $stmt->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        // PostgreSQL returns 't' or 'f' for boolean, or true/false depending on driver
        return $result === true || $result === 't';
    }

    /**
     * Check room availability using database function
     */
    public function getEquipmentAvailability()
    {
        $stmt = $this->conn->prepare("SELECT * FROM equipment_availability_view WHERE is_active = true ORDER BY display_order, equipment_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if Lab Inlet is available for the given time period
     */
    public function isLabInletAvailable($borrowDate, $returnDate)
    {
        $query = "SELECT COUNT(*) FROM $this->table 
                  WHERE type = 'room' 
                  AND room_name = 'Lab Inlet'
                  AND status = 'active'
                  AND (
                      (:borrow_date BETWEEN borrow_date AND return_date)
                      OR (:return_date BETWEEN borrow_date AND return_date)
                      OR (borrow_date BETWEEN :borrow_date AND :return_date)
                  )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':borrow_date', $borrowDate);
        $stmt->bindParam(':return_date', $returnDate);
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        return $count === 0;
    }

    // ========================================================================
    // STATISTICS & REPORTING
    // ========================================================================

    /**
     * Get borrowing statistics
     */
    public function getBorrowingStats()
    {
        $stats = [];
        
        // Total borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table");
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Active borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE status = 'active'");
        $stmt->execute();
        $stats['active'] = $stmt->fetchColumn();
        
        // Returned borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE status = 'returned'");
        $stmt->execute();
        $stats['returned'] = $stmt->fetchColumn();
        
        // Overdue borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE status = 'active' AND return_date < CURRENT_TIMESTAMP");
        $stmt->execute();
        $stats['overdue'] = $stmt->fetchColumn();
        
        // Equipment borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE type = 'equipment'");
        $stmt->execute();
        $stats['equipment'] = $stmt->fetchColumn();
        
        // Room borrowings
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE type = 'room'");
        $stmt->execute();
        $stats['room'] = $stmt->fetchColumn();
        
        return $stats;
    }

    /**
     * Get popular equipment
     */
    public function getPopularEquipment($limit = 10)
    {
        $query = "SELECT e.id, e.name, COUNT(be.id) as borrow_count
                  FROM equipment e
                  JOIN borrowing_equipment be ON e.id = be.equipment_id
                  GROUP BY e.id, e.name
                  ORDER BY borrow_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
