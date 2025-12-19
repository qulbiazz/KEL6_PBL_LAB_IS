<?php
/**
 * ajax_submit_guestbook.php
 * Handle AJAX submission for guestbook form
 * 
 * Created: 2025-11-28
 */

header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_POST['ajax']) || $_POST['ajax'] !== '1') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

try {
    // Include required files
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/GuestbookModel.php';

    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Initialize model
    $guestbookModel = new GuestbookModel($db);

    // Get form data
    $data = [
        'name' => $_POST['name'] ?? '',
        'institution' => $_POST['institution'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'message' => $_POST['message'] ?? ''
    ];

    // Get visitor information
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Create guestbook entry
    $result = $guestbookModel->create($data, $ip_address, $user_agent);

    // Return response
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Guestbook submission error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?>
