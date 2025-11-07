<?php

class ResearchModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getResearchById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM research_activities WHERE id = :id AND is_active = true");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllResearch($limit = null)
    {
        if ($limit) {
            $stmt = $this->conn->prepare("SELECT * FROM research_activities WHERE is_active = true ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->conn->query("SELECT * FROM research_activities WHERE is_active = true ORDER BY created_at DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
