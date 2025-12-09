<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
require_once '../models/BorrowingModel.php';
require_once '../models/SiteSettingsModel.php';
require_once '../helpers/EmailHelper.php';

$db = new Database();
$conn = $db->getConnection();
$borrowingModel = new BorrowingModel($conn);

// Handle approve borrowing
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $adminNotes = $_GET['notes'] ?? 'Peminjaman Anda telah disetujui.';
    
    if ($borrowingModel->updateApprovalStatus($id, 'approved', $adminNotes)) {
        // Send email notification to borrower
        try {
            error_log("Approval: Getting borrowing data for ID: {$id}");
            $borrowingData = $borrowingModel->getBorrowingForEmail($id);
            
            error_log("Approval: Borrowing data retrieved: " . json_encode($borrowingData));
            
            if ($borrowingData && !empty($borrowingData['borrower_email'])) {
                error_log("Approval: Sending approval email to: " . $borrowingData['borrower_email']);
                $emailResult = EmailHelper::sendApprovalNotification($borrowingData, 'approved', $adminNotes);
                error_log("Approval: Email result = " . ($emailResult ? 'SUCCESS' : 'FAILED'));
            } else {
                error_log("Approval: No email to send - borrower_email empty or data null");
            }
        } catch (Exception $e) {
            error_log('Failed to send approval email: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
        }
        
        $redirectParams = http_build_query([
            'success' => 'approved',
            'page' => $_GET['page'] ?? 1,
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ]);
        header('Location: borrowing.php?' . $redirectParams);
        exit();
    } else {
        $error = 'Gagal menyetujui peminjaman.';
    }
}

// Handle reject borrowing
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $adminNotes = $_GET['notes'] ?? 'Maaf, peminjaman Anda tidak dapat disetujui.';
    
    if ($borrowingModel->updateApprovalStatus($id, 'rejected', $adminNotes)) {
        // Restore items (equipment/room) without changing borrowing status
        $borrowingModel->restoreItemsOnly($id);
        
        // Send email notification to borrower
        try {
            error_log("Rejection: Getting borrowing data for ID: {$id}");
            $borrowingData = $borrowingModel->getBorrowingForEmail($id);
            
            error_log("Rejection: Borrowing data retrieved: " . json_encode($borrowingData));
            
            if ($borrowingData && !empty($borrowingData['borrower_email'])) {
                error_log("Rejection: Sending rejection email to: " . $borrowingData['borrower_email']);
                $emailResult = EmailHelper::sendApprovalNotification($borrowingData, 'rejected', $adminNotes);
                error_log("Rejection: Email result = " . ($emailResult ? 'SUCCESS' : 'FAILED'));
            } else {
                error_log("Rejection: No email to send - borrower_email empty or data null");
            }
        } catch (Exception $e) {
            error_log('Failed to send rejection email: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
        }
        
        $redirectParams = http_build_query([
            'success' => 'rejected',
            'page' => $_GET['page'] ?? 1,
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ]);
        header('Location: borrowing.php?' . $redirectParams);
        exit();
    } else {
        $error = 'Gagal menolak peminjaman.';
    }
}

// Handle return borrowing
if (isset($_GET['return'])) {
    $id = $_GET['return'];
    if ($borrowingModel->returnBorrowing($id)) {
        $redirectParams = http_build_query([
            'success' => 'returned',
            'page' => $_GET['page'] ?? 1,
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ]);
        header('Location: borrowing.php?' . $redirectParams);
        exit();
    } else {
        $error = 'Gagal mengembalikan peminjaman.';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($borrowingModel->deleteBorrowing($id)) {
        $redirectParams = http_build_query([
            'success' => 'deleted',
            'page' => $_GET['page'] ?? 1,
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? ''
        ]);
        header('Location: borrowing.php?' . $redirectParams);
        exit();
    } else {
        $error = 'Gagal menghapus data peminjaman.';
    }
}

// Handle update notification email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notification_email'])) {
    $newEmail = trim($_POST['borrowing_notification_email'] ?? '');
    
    if (!empty($newEmail) && filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $siteSettingsModel = new SiteSettingsModel($conn);
        $result = $siteSettingsModel->updateSettings(['borrowing_notification_email' => $newEmail], $_SESSION['admin_id'] ?? 1);
        
        if ($result['success']) {
            // Clear any output buffers
            if (ob_get_length()) ob_end_clean();
            
            $redirectParams = http_build_query([
                'success' => 'email_updated',
                'page' => $_GET['page'] ?? 1,
                'type' => $_GET['type'] ?? '',
                'status' => $_GET['status'] ?? ''
            ]);
            header('Location: borrowing.php?' . $redirectParams);
            exit();
        } else {
            $error = 'Gagal mengupdate email notifikasi: ' . ($result['message'] ?? 'Unknown error');
        }
    } else {
        $error = 'Format email tidak valid.';
    }
}

// Get filters (for front-end only)
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Fetch ALL borrowings without filter (filter will be done in front-end)
$borrowings = $borrowingModel->getAllBorrowings('', '', 1000, 0); // Get all data
$totalBorrowings = count($borrowings);
$totalPages = ceil($totalBorrowings / $perPage);

// Count by status
$activeCount = $borrowingModel->countBorrowings('', 'active');
$returnedCount = $borrowingModel->countBorrowings('', 'returned');

// Get notification email setting
$siteSettingsModel = new SiteSettingsModel($conn);
$settings = $siteSettingsModel->getSettings();
$notificationEmail = $settings['borrowing_notification_email'] ?? 'inlet@polinema.ac.id';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat dan Ruang - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* Custom scrollbar for table */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Peminjaman Alat dan Ruang</h1>
                <p class="text-gray-500 text-sm mt-1">Semua riwayat peminjaman alat dan ruangan</p>
            </div>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-xl mr-3"></i>
                        <span>
                            <?php if ($_GET['success'] === 'returned'): ?>
                                Peminjaman berhasil dikembalikan!
                            <?php elseif ($_GET['success'] === 'deleted'): ?>
                                Data peminjaman berhasil dihapus!
                            <?php elseif ($_GET['success'] === 'approved'): ?>
                                Peminjaman berhasil disetujui! Email notifikasi telah dikirim ke peminjam.
                            <?php elseif ($_GET['success'] === 'rejected'): ?>
                                Peminjaman berhasil ditolak. Email notifikasi telah dikirim ke peminjam.
                            <?php elseif ($_GET['success'] === 'email_updated'): ?>
                                Email notifikasi berhasil diupdate!
                            <?php endif; ?>
                        </span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-list text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Peminjaman</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $totalBorrowings; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-clock text-2xl text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $activeCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Sudah Dikembalikan</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $returnedCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Notification Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800">
                        <i class="fas fa-bell text-orange-500 mr-2"></i>Pengaturan Email Notifikasi
                    </h3>
                    <button type="button" onclick="toggleEmailSettings()" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-cog mr-1"></i>Ubah
                    </button>
                </div>
                <div id="emailSettingsDisplay" class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-sm text-gray-600">Email penerima notifikasi peminjaman:</p>
                        <p class="font-medium text-gray-900"><i class="fas fa-envelope mr-2 text-gray-400"></i><?php echo htmlspecialchars($notificationEmail); ?></p>
                    </div>
                </div>
                <form id="emailSettingsForm" method="POST" action="" class="hidden mt-4">
                    <input type="hidden" name="update_notification_email" value="1">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <label for="borrowing_notification_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Penerima Notifikasi
                            </label>
                            <input type="email" id="borrowing_notification_email" name="borrowing_notification_email" 
                                   value="<?php echo htmlspecialchars($notificationEmail); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="inlet@polinema.ac.id" required>
                            <p class="text-xs text-gray-500 mt-1">Email ini akan menerima notifikasi setiap ada peminjaman baru.</p>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition text-sm whitespace-nowrap">
                                <i class="fas fa-save mr-1"></i>Simpan
                            </button>
                            <button type="button" onclick="toggleEmailSettings()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition text-sm whitespace-nowrap">
                                Batal
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 mb-8">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-filter mr-2"></i>Filter Data
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                        <select id="filterType" onchange="applyFilters()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Jenis</option>
                            <option value="equipment">Alat</option>
                            <option value="room">Ruang</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="filterStatus" onchange="applyFilters()" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu Persetujuan</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="returned">Sudah Dikembalikan</option>
                        </select>
                    </div>
                    
                    <div class="sm:col-span-2 lg:col-span-1 flex items-end gap-2">
                        <button onclick="clearFilters()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition text-sm md:text-base">
                            <i class="fas fa-times mr-2"></i>Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                    Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                                    Pekerjaan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                    Waktu Pinjam</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                    Waktu Kembali</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                    Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                    Email Peminjam</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                                    Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($borrowings)): ?>
                                <tr class="borrowing-empty">
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Tidak ada data peminjaman</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($borrowings as $borrow): 
                                    // Determine display status for filtering
                                    $displayStatus = $borrow['status'] ?? '';
                                    if ($displayStatus === 'returned') {
                                        $filterStatus = 'returned';
                                    } elseif ($displayStatus === 'active') {
                                        $approvalStatus = $borrow['approval_status'] ?? 'pending';
                                        $filterStatus = $approvalStatus; // approved, rejected, or pending
                                    } else {
                                        $filterStatus = 'pending';
                                    }
                                ?>
                                    <tr class="borrowing-row hover:bg-gray-50" data-type="<?php echo htmlspecialchars($borrow['borrowing_type'] ?? ''); ?>" data-status="<?php echo htmlspecialchars($filterStatus); ?>">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-[130px]" title="<?php echo htmlspecialchars($borrow['borrower_name'] ?? '-'); ?>">
                                                <?php echo htmlspecialchars($borrow['borrower_name'] ?? '-'); ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-900 truncate block max-w-[110px]" title="<?php echo htmlspecialchars($borrow['occupation'] ?? '-'); ?>">
                                                <?php echo htmlspecialchars($borrow['occupation'] ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full whitespace-nowrap
                                                <?php echo ($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                                <?php echo ($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment' ? 'Alat' : 'Ruang'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php 
                                            // Display equipment names or room info based on type
                                            if (($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment') {
                                                $equipNames = $borrow['equipment_names'] ?? '-';
                                                echo '<div class="text-sm text-gray-900 max-w-xs">';
                                                echo '<span class="line-clamp-2" title="' . htmlspecialchars($equipNames) . '">' . htmlspecialchars($equipNames) . '</span>';
                                                echo '</div>';
                                            } else {
                                                // Room borrowing - show room name with button to view purpose
                                                echo '<div class="flex items-center gap-1.5 flex-wrap">';
                                                echo '<span class="font-medium text-purple-700 text-sm whitespace-nowrap">' . htmlspecialchars($borrow['room_name'] ?? 'Lab Inlet') . '</span>';
                                                if (!empty($borrow['notes'])) {
                                                    echo '<button onclick="showPurposeModal(\'' . htmlspecialchars(addslashes($borrow['notes']), ENT_QUOTES) . '\')" 
                                                            class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 px-2 py-0.5 rounded transition-colors whitespace-nowrap flex items-center gap-1">
                                                            <i class="fas fa-info-circle"></i>Keperluan
                                                          </button>';
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs text-gray-900 whitespace-nowrap block">
                                                <?php echo !empty($borrow['borrow_date']) ? date('d/m/Y H:i', strtotime($borrow['borrow_date'])) : '-'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs text-gray-900 whitespace-nowrap block">
                                                <?php echo !empty($borrow['return_date']) ? date('d/m/Y H:i', strtotime($borrow['return_date'])) : '-'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-1">
                                                <?php 
                                                $status = $borrow['status'] ?? 'active';
                                                $approvalStatus = $borrow['approval_status'] ?? 'pending';
                                                
                                                // Status peminjaman
                                                if ($status === 'returned') {
                                                    echo '<span class="px-2 py-1 inline-flex items-center text-xs font-semibold rounded-full whitespace-nowrap bg-green-100 text-green-800">';
                                                    echo '<i class="fas fa-check-circle mr-1"></i><span class="align-middle">Dikembalikan</span>';
                                                    echo '</span>';
                                                } else {
                                                    // Show approval status for active items
                                                    if ($approvalStatus === 'approved') {
                                                        echo '<span class="px-2 py-1 inline-flex items-center text-xs font-semibold rounded-full whitespace-nowrap bg-blue-100 text-blue-800">';
                                                        echo '<i class="fas fa-check-circle mr-1"></i><span class="align-middle">Disetujui</span>';
                                                        echo '</span>';
                                                    } elseif ($approvalStatus === 'rejected') {
                                                        echo '<span class="px-2 py-1 inline-flex items-center text-xs font-semibold rounded-full whitespace-nowrap bg-red-100 text-red-800">';
                                                        echo '<i class="fas fa-times-circle mr-1"></i><span class="align-middle">Ditolak</span>';
                                                        echo '</span>';
                                                    } else {
                                                        echo '<span class="px-2 py-1 inline-flex items-center text-xs font-semibold rounded-full whitespace-nowrap bg-yellow-100 text-yellow-800">';
                                                        echo '<i class="fas fa-clock mr-1"></i><span class="align-middle">Menunggu Persetujuan</span>';
                                                        echo '</span>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if (!empty($borrow['borrower_email'])): ?>
                                                <div class="text-sm text-gray-900 break-all truncate max-w-[130px]" title="<?php echo htmlspecialchars($borrow['borrower_email']); ?>">
                                                    <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($borrow['borrower_email']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <?php 
                                                $approvalStatus = $borrow['approval_status'] ?? 'pending';
                                                $status = $borrow['status'] ?? 'active';
                                                ?>
                                                
                                                <?php if ($approvalStatus === 'pending'): ?>
                                                    <button onclick="showApprovalModal(<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>, 'approve')" 
                                                        class="bg-green-50 hover:bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium transition-colors flex items-center whitespace-nowrap"
                                                        title="Setujui">
                                                        <i class="fas fa-check mr-1"></i>Setujui
                                                    </button>
                                                    <button onclick="showApprovalModal(<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>, 'reject')" 
                                                        class="bg-red-50 hover:bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium transition-colors flex items-center whitespace-nowrap"
                                                        title="Tolak">
                                                        <i class="fas fa-times mr-1"></i>Tolak
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($status === 'active' && $approvalStatus === 'approved'): ?>
                                                    <a href="?return=<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                                        onclick="return confirm('Tandai peminjaman ini sebagai sudah dikembalikan?')"
                                                        class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-medium transition-colors flex items-center whitespace-nowrap"
                                                        title="Kembalikan">
                                                        <i class="fas fa-undo mr-1"></i>Kembalikan
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="?delete=<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                                    onclick="return confirm('Hapus peminjaman ini?')"
                                                    class="bg-red-50 hover:bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium transition-colors flex items-center whitespace-nowrap"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden divide-y divide-gray-200">
                    <?php if (empty($borrowings)): ?>
                        <div class="borrowing-empty p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data peminjaman</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($borrowings as $borrow): 
                            // Determine display status for filtering
                            $displayStatus = $borrow['status'] ?? '';
                            if ($displayStatus === 'returned') {
                                $filterStatus = 'returned';
                            } elseif ($displayStatus === 'active') {
                                $approvalStatus = $borrow['approval_status'] ?? 'pending';
                                $filterStatus = $approvalStatus; // approved, rejected, or pending
                            } else {
                                $filterStatus = 'pending';
                            }
                        ?>
                            <div class="borrowing-row p-4 hover:bg-gray-50" data-type="<?php echo htmlspecialchars($borrow['borrowing_type'] ?? ''); ?>" data-status="<?php echo htmlspecialchars($filterStatus); ?>">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                                            <?php echo htmlspecialchars($borrow['borrower_name'] ?? '-'); ?>
                                        </h3>
                                        <p class="text-xs text-gray-600 truncate"><?php echo htmlspecialchars($borrow['occupation'] ?? '-'); ?></p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap flex-shrink-0
                                        <?php echo ($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                        <?php echo ($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment' ? 'Alat' : 'Ruang'; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-box text-gray-400 text-sm mt-0.5 flex-shrink-0"></i>
                                        <div class="flex-1 min-w-0">
                                            <?php 
                                            // Display equipment names or room info based on type
                                            if (($borrow['borrowing_type'] ?? $borrow['type'] ?? '') === 'equipment') {
                                                echo '<p class="text-sm text-gray-700 break-words">' . htmlspecialchars($borrow['equipment_names'] ?? '-') . '</p>';
                                            } else {
                                                // Room borrowing - show room name with button
                                                echo '<div class="flex flex-wrap items-center gap-2">';
                                                echo '<span class="font-medium text-purple-700 text-sm">' . htmlspecialchars($borrow['room_name'] ?? 'Lab Inlet') . '</span>';
                                                if (!empty($borrow['notes'])) {
                                                    echo '<button onclick="showPurposeModal(\'' . htmlspecialchars(addslashes($borrow['notes']), ENT_QUOTES) . '\')" 
                                                            class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 px-2 py-1 rounded transition-colors whitespace-nowrap">
                                                            <i class="fas fa-info-circle"></i> Keperluan
                                                          </button>';
                                                }
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-600 space-y-1.5 mb-3">
                                    <p class="flex items-center gap-2">
                                        <i class="fas fa-calendar-check text-gray-400 w-4 flex-shrink-0"></i>
                                        <span class="break-all">Pinjam: <?php echo !empty($borrow['borrow_date']) ? date('d/m/Y H:i', strtotime($borrow['borrow_date'])) : '-'; ?></span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <i class="fas fa-calendar-times text-gray-400 w-4 flex-shrink-0"></i>
                                        <span class="break-all">Kembali: <?php echo !empty($borrow['return_date']) ? date('d/m/Y H:i', strtotime($borrow['return_date'])) : '-'; ?></span>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <?php 
                                    $status = $borrow['status'] ?? 'active';
                                    $approvalStatus = $borrow['approval_status'] ?? 'pending';
                                    
                                    // Show combined status
                                    if ($status === 'returned') {
                                        echo '<span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap bg-green-100 text-green-800">';
                                        echo '<i class="fas fa-check-circle mr-1"></i>Dikembalikan';
                                        echo '</span>';
                                    } else {
                                        // Show approval status for active items
                                        if ($approvalStatus === 'approved') {
                                            echo '<span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap bg-blue-100 text-blue-800">';
                                            echo '<i class="fas fa-check-circle mr-1"></i>Disetujui';
                                            echo '</span>';
                                        } elseif ($approvalStatus === 'rejected') {
                                            echo '<span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap bg-red-100 text-red-800">';
                                            echo '<i class="fas fa-times-circle mr-1"></i>Ditolak';
                                            echo '</span>';
                                        } else {
                                            echo '<span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap bg-yellow-100 text-yellow-800">';
                                            echo '<i class="fas fa-clock mr-1"></i>Dipinjam';
                                            echo '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                                
                                <?php if (!empty($borrow['borrower_email'])): ?>
                                <div class="mb-3 pb-3 border-b border-gray-200">
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                        <span class="break-all"><?php echo htmlspecialchars($borrow['borrower_email']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                    
                                <div class="grid grid-cols-2 gap-2">
                                    <?php 
                                    $approvalPending = ($borrow['approval_status'] ?? 'pending') === 'pending';
                                    $isApproved = ($borrow['approval_status'] ?? 'pending') === 'approved';
                                    $isActive = ($borrow['status'] ?? 'active') === 'active';
                                    ?>
                                    
                                    <?php if ($approvalPending): ?>
                                        <button onclick="showApprovalModal(<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>, 'approve')" 
                                            class="bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1.5">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                        <button onclick="showApprovalModal(<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>, 'reject')" 
                                            class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1.5">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($isActive && $isApproved): ?>
                                        <a href="?return=<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                            onclick="return confirm('Tandai peminjaman ini sebagai sudah dikembalikan?')"
                                            class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1.5 <?php echo $approvalPending ? '' : 'col-span-2'; ?>">
                                            <i class="fas fa-undo"></i> Kembalikan
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?delete=<?php echo $borrow['borrowing_id'] ?? $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        onclick="return confirm('Hapus peminjaman ini?')"
                                        class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1.5 <?php echo (!$approvalPending && (!$isActive || !$isApproved)) ? 'col-span-2' : ''; ?>">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-sm text-gray-700">
                                Menampilkan <span class="font-medium"><?php echo min($offset + 1, $totalBorrowings); ?></span>
                                hingga <span class="font-medium"><?php echo min($offset + $perPage, $totalBorrowings); ?></span>
                                dari <span class="font-medium"><?php echo $totalBorrowings; ?></span> hasil
                            </div>
                            
                            <div class="flex gap-1">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                if ($startPage > 1): ?>
                                    <a href="?page=1&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        class="px-3 py-2 text-sm rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $totalPages; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50"><?php echo $totalPages; ?></a>
                                <?php endif; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Keperluan Peminjaman -->
    <div id="purposeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-lg bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>Keperluan Peminjaman
                </h3>
                <button onclick="closePurposeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-3">
                <p id="purposeContent" class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed"></p>
            </div>
            <div class="mt-6">
                <button onclick="closePurposeModal()" 
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Persetujuan/Penolakan -->
    <div id="approvalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-[450px] shadow-lg rounded-lg bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 id="approvalModalTitle" class="text-lg font-semibold text-gray-900">
                    <i id="approvalModalIcon" class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span id="approvalModalTitleText">Setujui Peminjaman</span>
                </h3>
                <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="approvalForm" method="GET" action="">
                <input type="hidden" id="approvalAction" name="approve" value="">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="type" value="<?php echo $filterType; ?>">
                <input type="hidden" name="status" value="<?php echo $filterStatus; ?>">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan untuk Peminjam (opsional)
                    </label>
                    <textarea id="approvalNotes" name="notes" rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        placeholder="Tambahkan catatan yang akan dikirim ke email peminjam..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeApprovalModal()" 
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="approvalSubmitBtn"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        <i class="fas fa-check mr-2"></i>Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPurposeModal(purpose) {
            document.getElementById('purposeContent').textContent = purpose;
            document.getElementById('purposeModal').classList.remove('hidden');
        }

        function closePurposeModal() {
            document.getElementById('purposeModal').classList.add('hidden');
        }
        
        function toggleEmailSettings() {
            const display = document.getElementById('emailSettingsDisplay');
            const form = document.getElementById('emailSettingsForm');
            
            if (form.classList.contains('hidden')) {
                display.classList.add('hidden');
                form.classList.remove('hidden');
            } else {
                display.classList.remove('hidden');
                form.classList.add('hidden');
            }
        }
        
        function showApprovalModal(borrowingId, action) {
            const modal = document.getElementById('approvalModal');
            const form = document.getElementById('approvalForm');
            const icon = document.getElementById('approvalModalIcon');
            const titleText = document.getElementById('approvalModalTitleText');
            const submitBtn = document.getElementById('approvalSubmitBtn');
            const actionInput = document.getElementById('approvalAction');
            const notesInput = document.getElementById('approvalNotes');
            
            // Reset notes
            notesInput.value = '';
            
            if (action === 'approve') {
                icon.className = 'fas fa-check-circle text-green-600 mr-2';
                titleText.textContent = 'Setujui Peminjaman';
                submitBtn.className = 'flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors';
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Setujui';
                actionInput.name = 'approve';
                actionInput.value = borrowingId;
                notesInput.placeholder = 'Contoh: Silakan ambil alat di Lab Inlet pada jam kerja (08:00-16:00)';
            } else {
                icon.className = 'fas fa-times-circle text-red-600 mr-2';
                titleText.textContent = 'Tolak Peminjaman';
                submitBtn.className = 'flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors';
                submitBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Tolak';
                actionInput.name = 'reject';
                actionInput.value = borrowingId;
                notesInput.placeholder = 'Contoh: Maaf, alat sedang dalam perawatan. Silakan coba minggu depan.';
            }
            
            modal.classList.remove('hidden');
        }
        
        function closeApprovalModal() {
            document.getElementById('approvalModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('purposeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePurposeModal();
            }
        });
        
        document.getElementById('approvalModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeApprovalModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePurposeModal();
                closeApprovalModal();
            }
        });

        // Add loading indicator on approval form submit
        document.getElementById('approvalForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('approvalSubmitBtn');
            const cancelBtn = submitBtn.previousElementSibling;
            const actionType = document.getElementById('approvalAction').name;
            
            // Disable buttons
            submitBtn.disabled = true;
            cancelBtn.disabled = true;
            
            // Change button appearance based on action
            if (actionType === 'approve') {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyetujui...';
                submitBtn.className = 'flex-1 bg-green-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed transition-all duration-300 flex items-center justify-center';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menolak...';
                submitBtn.className = 'flex-1 bg-red-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed transition-all duration-300 flex items-center justify-center';
            }
            
            // Don't prevent default - let form submit
        });

        // Front-end filter function
        function applyFilters() {
            const filterType = document.getElementById('filterType').value;
            const filterStatus = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.borrowing-row');
            
            console.log('Filter applied:', { filterType, filterStatus });
            
            let visibleCount = 0;
            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const rowStatus = row.getAttribute('data-status');
                
                console.log('Row data:', { rowType, rowStatus });
                
                const typeMatch = !filterType || rowType === filterType;
                const statusMatch = !filterStatus || rowStatus === filterStatus;
                
                if (typeMatch && statusMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            console.log('Visible rows:', visibleCount);
            
            // Update empty state
            const emptyRows = document.querySelectorAll('.borrowing-empty');
            emptyRows.forEach(emptyRow => {
                if (emptyRow) {
                    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                }
            });
        }

        // Clear filter function
        function clearFilters() {
            document.getElementById('filterType').value = '';
            document.getElementById('filterStatus').value = '';
            applyFilters();
        }
    </script>

    <script src="assets/js/admin.js"></script>
</body>
</html>