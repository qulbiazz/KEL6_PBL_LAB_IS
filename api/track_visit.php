<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $page = $data['page'] ?? '/';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO page_visits (page_url, visit_date, visit_count)
            VALUES (:page, CURRENT_DATE, 1)
            ON CONFLICT (page_url, visit_date)
            DO UPDATE SET visit_count = page_visits.visit_count + 1
        ");
        
        $stmt->execute(['page' => $page]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
