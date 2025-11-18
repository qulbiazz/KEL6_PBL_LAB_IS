<?php

class RoomModel
{
    private $conn;
    private $table = "rooms";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function createRoom($data)
    {
        $query = "INSERT INTO $this->table 
                  (name, description, capacity, display_order)
                  VALUES (:name, :description, :capacity, :display_order)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':display_order', $data['display_order']);

        return $stmt->execute();
    }

    // READ - Get all active rooms
    public function getAllRooms($activeOnly = true)
    {
        $query = "SELECT * FROM $this->table";
        
        if ($activeOnly) {
            $query .= " WHERE is_active = true";
        }
        
        $query .= " ORDER BY display_order ASC, name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Check if room is available for specific time range
    public function isRoomAvailable($roomId, $borrowDate, $returnDate, $excludeBorrowingId = null)
    {
        $query = "SELECT COUNT(*) as count FROM borrowings 
                  WHERE type = 'room' 
                  AND room_id = :room_id 
                  AND status = 'active'
                  AND (
                      (borrow_date <= :borrow_date AND return_date >= :borrow_date)
                      OR (borrow_date <= :return_date AND return_date >= :return_date)
                      OR (borrow_date >= :borrow_date AND return_date <= :return_date)
                  )";
        
        if ($excludeBorrowingId) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':borrow_date', $borrowDate);
        $stmt->bindParam(':return_date', $returnDate);
        
        if ($excludeBorrowingId) {
            $stmt->bindParam(':exclude_id', $excludeBorrowingId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }

    // READ - Get available rooms for specific time range
    public function getAvailableRooms($borrowDate, $returnDate)
    {
        $query = "SELECT r.* FROM $this->table r
                  WHERE r.is_active = true
                  AND r.id NOT IN (
                      SELECT DISTINCT room_id FROM borrowings
                      WHERE type = 'room'
                      AND room_id IS NOT NULL
                      AND status = 'active'
                      AND (
                          (borrow_date <= :borrow_date AND return_date >= :borrow_date)
                          OR (borrow_date <= :return_date AND return_date >= :return_date)
                          OR (borrow_date >= :borrow_date AND return_date <= :return_date)
                      )
                  )
                  ORDER BY r.display_order ASC, r.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':borrow_date', $borrowDate);
        $stmt->bindParam(':return_date', $returnDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get occupied rooms for specific time range
    public function getOccupiedRoomIds($borrowDate, $returnDate)
    {
        $query = "SELECT DISTINCT room_id FROM borrowings
                  WHERE type = 'room'
                  AND room_id IS NOT NULL
                  AND status = 'active'
                  AND (
                      (borrow_date <= :borrow_date AND return_date >= :borrow_date)
                      OR (borrow_date <= :return_date AND return_date >= :return_date)
                      OR (borrow_date >= :borrow_date AND return_date <= :return_date)
                  )";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':borrow_date', $borrowDate);
        $stmt->bindParam(':return_date', $returnDate);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($result, 'room_id');
    }

    // READ - Get by ID
    public function getRoomById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateRoom($id, $data)
    {
        $query = "UPDATE $this->table SET 
                    name = :name,
                    description = :description,
                    capacity = :capacity,
                    display_order = :display_order,
                    is_active = :is_active,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':display_order', $data['display_order']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // DELETE (soft delete)
    public function deleteRoom($id)
    {
        $stmt = $this->conn->prepare("UPDATE $this->table SET is_active = false WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // DELETE (permanent)
    public function permanentDeleteRoom($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Count total rooms
    public function countRooms()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE is_active = true");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
