<?php
header('Content-Type: application/json');

try {
    // Ambil parameter dari GET
    $borrowDate = $_GET['borrow_date'] ?? '';
    $returnDate = $_GET['return_date'] ?? '';

    // Validasi parameter
    if (empty($borrowDate) || empty($returnDate)) {
        echo json_encode([
            'available' => false,
            'error' => 'Parameter tanggal tidak lengkap'
        ]);
        exit;
    }

    // Koneksi database dan model
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/BorrowingModel.php';

    $database = new Database();
    $db = $database->getConnection();
    $borrowingModel = new BorrowingModel($db);

    // Cek ketersediaan Lab Inlet
    $available = $borrowingModel->isLabInletAvailable($borrowDate, $returnDate);

    echo json_encode([
        'available' => $available
    ]);

} catch (Exception $e) {
    echo json_encode([
        'available' => false,
        'error' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
