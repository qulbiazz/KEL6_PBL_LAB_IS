<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/BorrowingModel.php';
    require_once __DIR__ . '/../../models/EquipmentModel.php';
    require_once __DIR__ . '/../../models/RoomModel.php';

    // Ambil input
    $borrower_name = trim($_POST['nama'] ?? '');
    $occupation = trim($_POST['pekerjaan'] ?? '');
    if ($occupation === 'lainnya') {
        $occupation = trim($_POST['pekerjaan_lainnya'] ?? '');
    }
    $type = $_POST['jenis'] ?? '';
    $borrow_date = trim($_POST['waktu_pinjam'] ?? '');
    $return_date = trim($_POST['waktu_kembali'] ?? '');

    // Handle item based on type
    $item_name = '';
    $item_id = null;
    
    if ($type === 'room') {
        $item_id = $_POST['ruang'] ?? null;
        if (empty($item_id)) {
            throw new Exception('Ruang wajib dipilih!');
        }
    } elseif ($type === 'equipment') {
        if (isset($_POST['alat']) && is_array($_POST['alat'])) {
            $item_ids = $_POST['alat'];
            if (empty($item_ids)) {
                throw new Exception('Minimal pilih 1 alat!');
            }
            // For single equipment, store ID in item_id
            // For multiple equipment, store comma-separated IDs in item_name
            if (count($item_ids) === 1) {
                $item_id = $item_ids[0];
                $item_name = ''; // Will be filled later
            } else {
                $item_id = null;
                $item_name = implode(',', $item_ids);
            }
        } else {
            throw new Exception('Alat wajib dipilih!');
        }
    }

    // Validasi
    if (empty($borrower_name)) {
        throw new Exception('Nama lengkap wajib diisi!');
    }
    
    if (empty($occupation)) {
        throw new Exception('Pekerjaan wajib diisi!');
    }
    
    if (empty($type)) {
        throw new Exception('Jenis peminjaman wajib dipilih!');
    }
    
    if (!in_array($type, ['equipment', 'room'])) {
        throw new Exception('Tipe peminjaman tidak valid!');
    }
    
    if (empty($borrow_date)) {
        throw new Exception('Waktu peminjaman wajib diisi!');
    }
    
    if (!strtotime($borrow_date)) {
        throw new Exception('Format waktu peminjaman tidak valid!');
    }
    
    if (empty($return_date)) {
        throw new Exception('Waktu pengembalian wajib diisi!');
    }
    
    if (!strtotime($return_date)) {
        throw new Exception('Format waktu pengembalian tidak valid!');
    }
    
    if (strtotime($return_date) <= strtotime($borrow_date)) {
        throw new Exception('Waktu pengembalian harus lebih besar dari waktu peminjaman!');
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Additional validation for room availability
    if ($type === 'room') {
        $roomModel = new RoomModel($conn);
        if (!$roomModel->isRoomAvailable($item_id, $borrow_date, $return_date)) {
            throw new Exception('Ruang tersebut sudah dipinjam pada waktu yang dipilih!');
        }
        
        // Get room name
        $room = $roomModel->getRoomById($item_id);
        $item_name = $room['name'] ?? 'Unknown Room';
    }

    // Additional validation for equipment availability
    if ($type === 'equipment') {
        $equipmentModel = new EquipmentModel($conn);
        
        // Get list of equipment IDs
        if (!empty($item_id)) {
            // Single item
            $item_ids_array = [$item_id];
        } else {
            // Multiple items
            $item_ids_array = explode(',', $item_name);
        }
        
        $equipment_names = [];
        $equipment_ids = [];
        
        foreach ($item_ids_array as $eq_id) {
            $eq_id = trim($eq_id);
            $equipment = $equipmentModel->getEquipmentById($eq_id);
            if (!$equipment || $equipment['available_quantity'] <= 0) {
                throw new Exception('Alat "' . ($equipment['name'] ?? 'Unknown') . '" tidak tersedia!');
            }
            $equipment_names[] = $equipment['name'];
            $equipment_ids[] = $eq_id;
            
            // Decrease available quantity
            $equipmentModel->decreaseAvailableQuantity($eq_id, 1);
        }
        
        // Update item_name with actual names for display
        $item_name = implode(', ', $equipment_names);
        
        // If single item, keep item_id, otherwise store IDs in item_name
        if (count($equipment_ids) > 1) {
            $item_id = null;
            // Also store IDs for return process - prepend with "IDS:"
            $item_name = 'IDS:' . implode(',', $equipment_ids) . '|' . $item_name;
        }
    }

    // Save to database
    $borrowingModel = new BorrowingModel($conn);
    $data = [
        'borrower_name' => $borrower_name,
        'occupation' => $occupation,
        'type' => $type,
        'item_id' => $item_id,
        'item_name' => $item_name,
        'borrow_date' => $borrow_date,
        'return_date' => $return_date,
        'status' => 'active'
    ];

    if ($borrowingModel->createBorrowing($data)) {
        echo json_encode([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan! Silakan hubungi admin untuk konfirmasi.'
        ]);
    } else {
        throw new Exception('Gagal menyimpan data peminjaman!');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error sistem: ' . $e->getMessage()
    ]);
}
