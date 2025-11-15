<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
require_once '../models/BorrowingModel.php';

$db = new Database();
$conn = $db->getConnection();
$borrowingModel = new BorrowingModel($conn);

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

// Get filters
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';

// Pagination
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Fetch borrowings with filters
$borrowings = $borrowingModel->getAllBorrowings($filterType, $filterStatus, $perPage, $offset);
$totalBorrowings = $borrowingModel->countBorrowings($filterType, $filterStatus);
$totalPages = ceil($totalBorrowings / $perPage);

// Count by status
$activeCount = $borrowingModel->countBorrowings('', 'active');
$returnedCount = $borrowingModel->countBorrowings('', 'returned');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat dan Ruang - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8 mt-12">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Log Peminjaman Alat dan Ruang</h1>
                <p class="text-gray-600 mt-1">Semua riwayat peminjaman alat dan ruangan</p>
            </div>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php if ($_GET['success'] === 'returned'): ?>
                        Peminjaman berhasil dikembalikan!
                    <?php elseif ($_GET['success'] === 'deleted'): ?>
                        Data peminjaman berhasil dihapus!
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
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
                
                <div class="bg-white rounded-lg shadow p-6">
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
                
                <div class="bg-white rounded-lg shadow p-6">
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

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-filter mr-2"></i>Filter Data
                </h3>
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="page" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Jenis</option>
                            <option value="equipment" <?php echo $filterType === 'equipment' ? 'selected' : ''; ?>>Alat</option>
                            <option value="room" <?php echo $filterType === 'room' ? 'selected' : ''; ?>>Ruang</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
                            <option value="returned" <?php echo $filterStatus === 'returned' ? 'selected' : ''; ?>>Sudah Dikembalikan</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-search mr-2"></i>Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pekerjaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu Pinjam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu Kembali</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($borrowings)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Tidak ada data peminjaman</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($borrowings as $borrow): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($borrow['borrower_name'] ?? '-'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($borrow['occupation'] ?? '-'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs font-semibold rounded-full 
                                                <?php echo ($borrow['type'] ?? '') === 'equipment' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                                <?php echo ($borrow['type'] ?? '') === 'equipment' ? 'Alat' : 'Ruang'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php echo htmlspecialchars($borrow['item_display_name'] ?? $borrow['item_name'] ?? '-'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo !empty($borrow['borrow_date']) ? date('d/m/Y H:i', strtotime($borrow['borrow_date'])) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo !empty($borrow['return_date']) ? date('d/m/Y H:i', strtotime($borrow['return_date'])) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php 
                                            $status = $borrow['status'] ?? 'active';
                                            $statusClass = $status === 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                                            $statusText = $status === 'active' ? 'Dipinjam' : 'Dikembalikan';
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if (($borrow['status'] ?? 'active') === 'active'): ?>
                                                <a href="?return=<?php echo $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                                    onclick="return confirm('Tandai peminjaman ini sebagai sudah dikembalikan?')"
                                                    class="text-green-600 hover:text-green-900 mr-3" title="Kembalikan">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                                onclick="return confirm('Hapus peminjaman ini?')"
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-200">
                    <?php if (empty($borrowings)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data peminjaman</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($borrowings as $borrow): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($borrow['borrower_name'] ?? '-'); ?>
                                        </h3>
                                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($borrow['occupation'] ?? '-'); ?></p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo ($borrow['type'] ?? '') === 'equipment' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'; ?>">
                                        <?php echo ($borrow['type'] ?? '') === 'equipment' ? 'Alat' : 'Ruang'; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-box text-gray-400 mr-2"></i>
                                        <?php echo htmlspecialchars($borrow['item_display_name'] ?? $borrow['item_name'] ?? '-'); ?>
                                    </p>
                                </div>

                                <div class="text-xs text-gray-600 space-y-1 mb-3">
                                    <p>
                                        <i class="fas fa-calendar-check text-gray-400 mr-2"></i>
                                        Pinjam: <?php echo !empty($borrow['borrow_date']) ? date('d/m/Y H:i', strtotime($borrow['borrow_date'])) : '-'; ?>
                                    </p>
                                    <p>
                                        <i class="fas fa-calendar-times text-gray-400 mr-2"></i>
                                        Kembali: <?php echo !empty($borrow['return_date']) ? date('d/m/Y H:i', strtotime($borrow['return_date'])) : '-'; ?>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between">
                                    <?php 
                                    $status = $borrow['status'] ?? 'active';
                                    $statusClass = $status === 'active' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                                    $statusText = $status === 'active' ? 'Dipinjam' : 'Dikembalikan';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                    
                                    <div class="flex space-x-3">
                                        <?php if (($borrow['status'] ?? 'active') === 'active'): ?>
                                            <a href="?return=<?php echo $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                                onclick="return confirm('Tandai peminjaman ini sebagai sudah dikembalikan?')"
                                                class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $borrow['id']; ?>&page=<?php echo $page; ?>&type=<?php echo $filterType; ?>&status=<?php echo $filterStatus; ?>" 
                                            onclick="return confirm('Hapus peminjaman ini?')"
                                            class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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

        <script src="../assets/js/admin.js"></script>
</body>

</html>