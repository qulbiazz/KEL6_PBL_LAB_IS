<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable to see errors
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log'); // Log errors to file

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/BorrowingModel.php';
    require_once __DIR__ . '/../../models/EquipmentModel.php';
    require_once __DIR__ . '/../../models/SiteSettingsModel.php';
    require_once __DIR__ . '/../../helpers/EmailHelper.php';

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
    $purpose = null;
    
    if ($type === 'room') {
        $purpose = trim($_POST['keperluan_ruang'] ?? '');
        if (empty($purpose)) {
            throw new Exception('Keperluan peminjaman wajib diisi!');
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

    // Save to database using optimized BorrowingModel
    $borrowingModel = new BorrowingModel($conn);
    
    if ($type === 'room') {
        // Room borrowing - Lab Inlet only
        $data = [
            'borrower_name' => $borrower_name,
            'borrower_email' => $_POST['email'] ?? null,
            'occupation' => $occupation,
            'room_name' => 'Lab Inlet',
            'borrow_date' => $borrow_date,
            'return_date' => $return_date,
            'status' => 'active',
            'notes' => $purpose
        ];
        
        try {
            $result = $borrowingModel->createRoomBorrowing($data);
        } catch (Exception $e) {
            // Re-throw the exception to be caught by outer try-catch
            throw $e;
        }
        
    } elseif ($type === 'equipment') {
        // Equipment borrowing
        $equipmentModel = new EquipmentModel($conn);
        
        // Get list of equipment IDs
        if (!empty($item_id)) {
            // Single item
            $item_ids_array = [$item_id];
        } else {
            // Multiple items from comma-separated string
            $item_ids_array = explode(',', $item_name);
        }
        
        // Prepare equipment items array for junction table
        $equipmentItems = [];
        foreach ($item_ids_array as $eq_id) {
            $eq_id = trim($eq_id);
            if (empty($eq_id) || !is_numeric($eq_id)) {
                continue;
            }
            
            // Validate equipment exists and available
            $equipment = $equipmentModel->getEquipmentById($eq_id);
            if (!$equipment) {
                throw new Exception('Alat dengan ID ' . $eq_id . ' tidak ditemukan!');
            }
            
            // Check availability using optimized model method
            if (!$borrowingModel->checkEquipmentAvailability($eq_id, 1)) {
                throw new Exception('Alat "' . $equipment['name'] . '" tidak tersedia!');
            }
            
            $equipmentItems[] = [
                'equipment_id' => intval($eq_id),
                'quantity' => 1
            ];
        }
        
        if (empty($equipmentItems)) {
            throw new Exception('Tidak ada alat yang valid untuk dipinjam!');
        }
        
        $data = [
            'borrower_name' => $borrower_name,
            'borrower_email' => $_POST['email'] ?? null,
            'occupation' => $occupation,
            'borrow_date' => $borrow_date,
            'return_date' => $return_date,
            'status' => 'active',
            'notes' => null
        ];
        
        $result = $borrowingModel->createEquipmentBorrowing($data, $equipmentItems);
    } else {
        throw new Exception('Tipe peminjaman tidak valid!');
    }

    if ($result) {
        // Send email notification to admin
        try {
            $siteSettingsModel = new SiteSettingsModel($conn);
            $settings = $siteSettingsModel->getSettings();
            $adminEmail = $settings['borrowing_notification_email'] ?? 'inlet@polinema.ac.id';
            
            // Prepare email data
            $emailData = [
                'borrower_name' => $borrower_name,
                'borrower_email' => $_POST['email'] ?? 'N/A',
                'occupation' => $occupation,
                'type' => $type,
                'item_name' => ($type === 'room') ? 'Lab Inlet' : $item_name,
                'borrow_date' => $borrow_date,
                'return_date' => $return_date,
                'notes' => ($type === 'room') ? $purpose : ($_POST['keperluan'] ?? '')
            ];
            
            // If equipment, get equipment names instead of IDs
            if ($type === 'equipment') {
                $equipmentModel = new EquipmentModel($conn);
                $equipmentNames = [];
                foreach ($equipmentItems as $item) {
                    $eq = $equipmentModel->getEquipmentById($item['equipment_id']);
                    if ($eq) {
                        $equipmentNames[] = $eq['name'];
                    }
                }
                $emailData['item_name'] = implode(', ', $equipmentNames);
            }
            
            // Send email
            EmailHelper::sendBorrowingNotificationToAdmin($emailData, $adminEmail);
            
        } catch (Exception $emailEx) {
            // Log email error but don't fail the borrowing
            error_log('Email notification failed: ' . $emailEx->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan! Silakan menunggu email konfirmasi dari admin.'
        ]);
    } else {
        throw new Exception('Gagal menyimpan data peminjaman!');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error sistem: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
