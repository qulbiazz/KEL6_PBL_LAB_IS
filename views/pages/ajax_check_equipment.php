<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/EquipmentModel.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $equipmentModel = new EquipmentModel($conn);
    
    // Get all equipment
    $allEquipment = $equipmentModel->getAllEquipment(true);
    
    if (!is_array($allEquipment)) {
        throw new Exception('Failed to fetch equipment data');
    }
    
    echo json_encode([
        'success' => true,
        'equipment' => $allEquipment,
        'count' => count($allEquipment)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
