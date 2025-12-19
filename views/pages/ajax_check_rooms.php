<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $borrowDate = $_GET['borrow_date'] ?? '';
    $returnDate = $_GET['return_date'] ?? '';
    
    if (empty($borrowDate) || empty($returnDate)) {
        throw new Exception('Tanggal peminjaman dan pengembalian wajib diisi');
    }
    
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/RoomModel.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // $roomModel = new RoomModel($conn);
    
    // Get all rooms
    $allRooms = $roomModel->getAllRooms(true);
    
    if (!is_array($allRooms)) {
        throw new Exception('Failed to fetch rooms data');
    }
    
    // Get occupied room IDs
    $occupiedIds = $roomModel->getOccupiedRoomIds($borrowDate, $returnDate);
    
    // Mark rooms as available or occupied
    foreach ($allRooms as &$room) {
        $room['is_available'] = !in_array($room['id'], $occupiedIds);
    }
    
    echo json_encode([
        'success' => true,
        'rooms' => $allRooms,
        'count' => count($allRooms),
        'occupied_ids' => $occupiedIds
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
