<?php
// Disable all caching aggressively
header('Content-Type: application/json');
header_remove('Cache-Control');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header_remove('Pragma');
header('Pragma: no-cache');
header_remove('Expires');
header('Expires: 0');

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
    
    // Get all active equipment (including those with 0 quantity for display)
    $allEquipment = $equipmentModel->getAllEquipment(true);
    
    if (!is_array($allEquipment)) {
        throw new Exception('Failed to fetch equipment data');
    }
    
    // Ensure quantity fields are integers
    foreach ($allEquipment as &$equipment) {
        $equipment['quantity'] = (int)$equipment['quantity'];
        $equipment['available_quantity'] = (int)$equipment['available_quantity'];
        $equipment['is_active'] = (bool)$equipment['is_active'];
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
