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
                  (borrower_name, occupation, type, item_name, borrow_date, return_date)
                  VALUES (:borrower_name, :occupation, :type, :item_name, :borrow_date, :return_date)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':borrower_name', $data['borrower_name']);
        $stmt->bindParam(':occupation', $data['occupation']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':item_name', $data['item_name']);
        $stmt->bindParam(':borrow_date', $data['borrow_date']);
        $stmt->bindParam(':return_date', $data['return_date']);

        return $stmt->execute();
    }

    // READ
    public function getAllBorrowings($type = null, $limit = 20, $offset = 0)
    {
        $query = "SELECT * FROM $this->table WHERE 1=1";

        if (!empty($type)) {
            $query .= " AND type = :type";
        }

        $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countBorrowings($type = null)
    {
        $query = "SELECT COUNT(*) AS total FROM $this->table WHERE 1=1";

        if (!empty($type)) {
            $query .= " AND type = :type";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
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
                    borrow_date = :borrow_date,
                    return_date = :return_date
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':borrower_name', $data['borrower_name']);
        $stmt->bindParam(':occupation', $data['occupation']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':item_name', $data['item_name']);
        $stmt->bindParam(':borrow_date', $data['borrow_date']);
        $stmt->bindParam(':return_date', $data['return_date']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // DELETE
    public function deleteBorrowing($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
