<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/AttendanceModel.php';

$db = new Database();
$conn = $db->getConnection();
$attendanceModel = new AttendanceModel($conn);

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    if ($attendanceModel->deleteAttendance($_GET['delete'])) {
        $success = 'Data absensi berhasil dihapus';
    } else {
        $error = 'Gagal menghapus data absensi';
    }
}

// Handle Export
if (isset($_GET['export'])) {
    $date = $_GET['filter_date'] ?? null;
    $nim = $_GET['filter_nim'] ?? null;
    $type = $_GET['filter_type'] ?? null;
    
    $filename = $attendanceModel->exportToCSV($date, $nim, $type);
    
    if ($filename) {
        $filepath = '../uploads/exports/' . $filename;
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filepath);
        unlink($filepath); // Hapus file setelah didownload
        exit();
    } else {
        $error = 'Tidak ada data untuk diekspor';
    }
}

// Get filters
$filter_date = $_GET['filter_date'] ?? null;
$filter_nim = $_GET['filter_nim'] ?? null;
$filter_type = $_GET['filter_type'] ?? null;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get data
$attendances = $attendanceModel->getAllAttendance($filter_date, $filter_nim, $filter_type, $limit, $offset);
$total_records = $attendanceModel->countAttendance($filter_date, $filter_nim, $filter_type);
$total_pages = ceil($total_records / $limit);

// Get today's statistics
$today_stats = $attendanceModel->getAttendanceStats(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mahasiswa - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8 mt-12">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Absensi Mahasiswa</h1>
                <p class="text-gray-600 mt-1">Kelola dan pantau absensi mahasiswa</p>
            </div>

            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">Absen Datang Hari Ini</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $today_stats['total_datang']; ?></p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full">
                            <i class="fas fa-arrow-right text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">Absen Pulang Hari Ini</p>
                            <p class="text-3xl font-bold text-red-600 mt-2"><?php echo $today_stats['total_pulang']; ?></p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-full">
                            <i class="fas fa-arrow-left text-2xl text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-semibold uppercase">Total Mahasiswa</p>
                            <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $today_stats['unique_students']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full">
                            <i class="fas fa-users text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>Filter Data
                </h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">Tanggal</label>
                        <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filter_date ?? ''); ?>"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">NIM</label>
                        <input type="text" name="filter_nim" value="<?php echo htmlspecialchars($filter_nim ?? ''); ?>"
                            placeholder="Cari NIM..."
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">Tipe</label>
                        <select name="filter_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua</option>
                            <option value="datang" <?php echo $filter_type === 'datang' ? 'selected' : ''; ?>>Datang</option>
                            <option value="pulang" <?php echo $filter_type === 'pulang' ? 'selected' : ''; ?>>Pulang</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="attendance.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-block">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="?export=1<?php echo $filter_date ? '&filter_date=' . $filter_date : ''; ?><?php echo $filter_nim ? '&filter_nim=' . $filter_nim : ''; ?><?php echo $filter_type ? '&filter_type=' . $filter_type : ''; ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-file-export mr-2"></i>Export CSV
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($attendances)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada data absensi</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($attendances as $attendance): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $attendance['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($attendance['nama']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($attendance['nim']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex items-center gap-2 text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $attendance['attendance_type'] === 'datang' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <i class="fas fa-arrow-<?php echo $attendance['attendance_type'] === 'datang' ? 'right' : 'left'; ?>"></i>
                                            <span><?php echo ucfirst($attendance['attendance_type']); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($attendance['attendance_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('H:i:s', strtotime($attendance['attendance_time'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($attendance['ip_address'] ?? '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="?delete=<?php echo $attendance['id']; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" 
                                           onclick="return confirm('Yakin ingin menghapus data ini?')"
                                           class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan <?php echo min($offset + 1, $total_records); ?> - <?php echo min($offset + $limit, $total_records); ?> dari <?php echo $total_records; ?> data
                        </div>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $filter_date ? '&filter_date=' . $filter_date : ''; ?><?php echo $filter_nim ? '&filter_nim=' . $filter_nim : ''; ?><?php echo $filter_type ? '&filter_type=' . $filter_type : ''; ?>" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $filter_date ? '&filter_date=' . $filter_date : ''; ?><?php echo $filter_nim ? '&filter_nim=' . $filter_nim : ''; ?><?php echo $filter_type ? '&filter_type=' . $filter_type : ''; ?>" 
                                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $filter_date ? '&filter_date=' . $filter_date : ''; ?><?php echo $filter_nim ? '&filter_nim=' . $filter_nim : ''; ?><?php echo $filter_type ? '&filter_type=' . $filter_type : ''; ?>" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
