<?php

class HomeModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getBanners()
    {
        $stmt = $this->conn->query("SELECT * FROM carousel_banners WHERE is_active = true ORDER BY display_order");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCards($limit = 4)
    {
        $stmt = $this->conn->prepare("SELECT * FROM card_data WHERE is_active = true ORDER BY display_order LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProducts($limit = 3)
    {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE is_active = true ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPartners()
    {
        $stmt = $this->conn->query("SELECT * FROM partners WHERE is_active = true ORDER BY display_order");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeamMembers($limit = 8)
    {
        $stmt = $this->conn->prepare("SELECT * FROM team_members WHERE is_active = true ORDER BY display_order LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNews($limit = 3)
    {
        $stmt = $this->conn->prepare("SELECT * FROM news WHERE is_active = true ORDER BY published_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGallery($limit = 12)
    {
        $stmt = $this->conn->prepare("SELECT * FROM gallery WHERE is_active = true ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResearchActivities($limit = 3)
    {
        $stmt = $this->conn->prepare("SELECT * FROM research_activities WHERE is_active = true ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
