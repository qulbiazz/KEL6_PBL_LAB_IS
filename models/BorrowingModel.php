<?php

class BorrowingModel
{
    private $conn;
    private $table = "borrowings";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function createBorrowing($data)
    {
        $query = "INSERT INTO $this->table 
                  (borrower_name, occupation, type, item_name, item_id, item_type, borrow_date, return_date, status)
                  VALUES (:borrower_name, :occupation, :type, :item_name, :item_id, :item_type, :borrow_date, :return_date, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':borrower_name', $data['borrower_name']);
        $stmt->bindParam(':occupation', $data['occupation']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':item_name', $data['item_name']);
        
        // Handle item_id (bisa NULL untuk multiple equipment)
        if (isset($data['item_id']) && !empty($data['item_id'])) {
            $stmt->bindParam(':item_id', $data['item_id']);
        } else {
            $stmt->bindValue(':item_id', null, PDO::PARAM_NULL);
        }
        
        // Handle item_type
        $item_type = $data['item_type'] ?? $data['type'];
        $stmt->bindParam(':item_type', $item_type);
        
        $stmt->bindParam(':borrow_date', $data['borrow_date']);
        $stmt->bindParam(':return_date', $data['return_date']);
        
        // Handle status (default: active)
        $status = $data['status'] ?? 'active';
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    // READ
    public function getAllBorrowings($type = null, $status = null, $limit = 100, $offset = 0)
    {
        $query = "SELECT b.*, 
                  CASE 
                      WHEN b.item_type = 'room' THEN r.name
                      WHEN b.item_type = 'equipment' AND b.item_id IS NOT NULL THEN e.name
                      WHEN b.item_type = 'equipment' AND b.item_name LIKE 'IDS:%' THEN SUBSTRING(b.item_name, POSITION('|' IN b.item_name) + 1)
                      ELSE b.item_name
                  END as item_display_name,
                  CASE 
                      WHEN b.item_type = 'room' THEN r.description
                      WHEN b.item_type = 'equipment' THEN e.description
                      ELSE NULL
                  END as item_description
                  FROM $this->table b
                  LEFT JOIN rooms r ON b.item_type = 'room' AND b.item_id = r.id
                  LEFT JOIN equipment e ON b.item_type = 'equipment' AND b.item_id = e.id
                  WHERE 1=1";

        if (!empty($type)) {
            $query .= " AND b.type = :type";
        }
        
        if (!empty($status)) {
            $query .= " AND b.status = :status";
        }

        $query .= " ORDER BY b.id DESC LIMIT :limit OFFSET :offset";

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

    public function getBorrowingById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateBorrowing($id, $data)
    {
        $query = "UPDATE $this->table SET 
                    borrower_name = :borrower_name,
                    occupation = :occupation,
                    type = :type,
                    item_name = :item_name,
                    item_id = :item_id,
                    item_type = :item_type,
                    borrow_date = :borrow_date,
                    return_date = :return_date,
                    status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':borrower_name', $data['borrower_name']);
        $stmt->bindParam(':occupation', $data['occupation']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':item_name', $data['item_name']);
        
        if (isset($data['item_id']) && !empty($data['item_id'])) {
            $stmt->bindParam(':item_id', $data['item_id']);
        } else {
            $stmt->bindValue(':item_id', null, PDO::PARAM_NULL);
        }
        
        $item_type = $data['item_type'] ?? $data['type'];
        $stmt->bindParam(':item_type', $item_type);
        
        $stmt->bindParam(':borrow_date', $data['borrow_date']);
        $stmt->bindParam(':return_date', $data['return_date']);
        
        $status = $data['status'] ?? 'active';
        $stmt->bindParam(':status', $status);
        
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
    
    // UPDATE STATUS
    public function updateStatus($id, $status)
    {
        $query = "UPDATE $this->table SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // RETURN BORROWING
    public function returnBorrowing($id)
    {
        // Get borrowing data first
        $borrowing = $this->getBorrowingById($id);
        
        if (!$borrowing) {
            return false;
        }
        
        // If it's equipment, increase available quantity
        if ($borrowing['type'] === 'equipment') {
            require_once __DIR__ . '/EquipmentModel.php';
            $equipmentModel = new EquipmentModel($this->conn);
            
            // Check if single item with item_id
            if (!empty($borrowing['item_id']) && is_numeric($borrowing['item_id'])) {
                // Single equipment item
                $equipmentModel->increaseAvailableQuantity($borrowing['item_id'], 1);
            } 
            // Check if multiple items with IDS: prefix
            elseif (!empty($borrowing['item_name']) && strpos($borrowing['item_name'], 'IDS:') === 0) {
                // Multiple equipment items - extract IDs
                $parts = explode('|', $borrowing['item_name']);
                $ids_part = str_replace('IDS:', '', $parts[0]);
                $equipment_ids = explode(',', $ids_part);
                
                foreach ($equipment_ids as $eq_id) {
                    $eq_id = trim($eq_id);
                    if (is_numeric($eq_id)) {
                        $equipmentModel->increaseAvailableQuantity($eq_id, 1);
                    }
                }
            }
        }
        
        // Update status to returned
        return $this->updateStatus($id, 'returned');
    }

    // DELETE
    public function deleteBorrowing($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
