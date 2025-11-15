<?php

class EquipmentModel
{
    private $conn;
    private $table = "equipment";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function createEquipment($data)
    {
        $query = "INSERT INTO $this->table 
                  (name, description, quantity, available_quantity, display_order)
                  VALUES (:name, :description, :quantity, :available_quantity, :display_order)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':available_quantity', $data['available_quantity']);
        $stmt->bindParam(':display_order', $data['display_order']);

        return $stmt->execute();
    }

    // READ - Get all active equipment
    public function getAllEquipment($activeOnly = true)
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

    // READ - Get available equipment (yang masih ada stock)
    public function getAvailableEquipment()
    {
        $query = "SELECT * FROM $this->table 
                  WHERE is_active = true AND available_quantity > 0
                  ORDER BY display_order ASC, name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - Get by ID
    public function getEquipmentById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateEquipment($id, $data)
    {
        $query = "UPDATE $this->table SET 
                    name = :name,
                    description = :description,
                    quantity = :quantity,
                    available_quantity = :available_quantity,
                    display_order = :display_order,
                    is_active = :is_active,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':available_quantity', $data['available_quantity']);
        $stmt->bindParam(':display_order', $data['display_order']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // UPDATE - Decrease available quantity when borrowed
    public function decreaseAvailableQuantity($id, $amount = 1)
    {
        $query = "UPDATE $this->table SET 
                    available_quantity = available_quantity - :amount
                  WHERE id = :id AND available_quantity >= :amount";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':amount', $amount);

        return $stmt->execute();
    }

    // UPDATE - Increase available quantity when returned
    public function increaseAvailableQuantity($id, $amount = 1)
    {
        $query = "UPDATE $this->table SET 
                    available_quantity = LEAST(available_quantity + :amount, quantity)
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':amount', $amount);

        return $stmt->execute();
    }

    // DELETE (soft delete)
    public function deleteEquipment($id)
    {
        $stmt = $this->conn->prepare("UPDATE $this->table SET is_active = false WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // DELETE (permanent)
    public function permanentDeleteEquipment($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Count total equipment
    public function countEquipment()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM $this->table WHERE is_active = true");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
