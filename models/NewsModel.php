<?php

class NewsModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getNewsById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM news WHERE id = :id AND is_active = true");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllNews($limit = null)
    {
        if ($limit) {
            $stmt = $this->conn->prepare("SELECT * FROM news WHERE is_active = true ORDER BY published_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->conn->query("SELECT * FROM news WHERE is_active = true ORDER BY published_at DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOtherNews($current_id, $limit = 3)
    {
        $stmt = $this->conn->prepare("SELECT * FROM news WHERE id != :current_id AND is_active = true ORDER BY published_at DESC LIMIT :limit");
        $stmt->bindValue(':current_id', $current_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
