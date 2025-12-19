<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';
require_once '../models/GuestbookModel.php';
require_once '../vendor/autoload.php';

$db = new Database();
$conn = $db->getConnection();
$guestbookModel = new GuestbookModel($conn);

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($guestbookModel->delete($id)) {
        $redirectParams = http_build_query([
            'success' => 'deleted',
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'institution' => $_GET['institution'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ]);
        header('Location: guestbook.php?' . $redirectParams);
        exit();
    } else {
        $error = 'Gagal menghapus data buku tamu.';
    }
}

// Handle export
if (isset($_GET['export'])) {
    $filters = [
        'search' => $_GET['search'] ?? '',
        'institution' => $_GET['institution'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? ''
    ];

    $filename = $guestbookModel->exportToExcel($filters);

    if ($filename) {
        $filepath = '../uploads/exports/' . $filename;

        if (file_exists($filepath)) {
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));

            readfile($filepath);
            unlink($filepath); // Delete after download
            exit();
        }
    }

    $error = 'Gagal mengekspor data.';
}

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'institution' => $_GET['institution'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Pagination
$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Fetch guestbook entries
$entries = $guestbookModel->getAll($filters, $perPage, $offset);
$totalEntries = $guestbookModel->count($filters);
$totalPages = ceil($totalEntries / $perPage);

// Get statistics
$stats = $guestbookModel->getStats();

// Get unique institutions for filter
$institutions = $guestbookModel->getUniqueInstitutions();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom scrollbar */
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

<body class="bg-gray-50">
    <?php include 'includes/admin_navbar.php'; ?>
    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <div class="lg:ml-64 min-h-screen flex-1">


            <main class="p-6 lg:p-8 pt-24 md:pt-28">
                <!-- Page Header -->
                <div class="mb-8 lg:mt-16 md:mt-12 sm:mt-0">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">
                                <i class="fas fa-book-open text-blue-600 mr-2"></i>
                                Buku Tamu
                            </h1>
                            <p class="text-gray-600 mt-1">Kelola dan pantau pesan dari pengunjung</p>
                        </div>
                        <button
                            onclick="window.location.href='guestbook.php?export=1&<?php echo http_build_query($filters); ?>'"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            <span class="hidden sm:inline">Export Excel</span>
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if (isset($_GET['success'])): ?>
                    <div
                        class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle text-xl mr-3"></i>
                        <span>
                            <?php
                            if ($_GET['success'] === 'deleted') {
                                echo 'Data buku tamu berhasil dihapus.';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Total Entries</p>
                                <h3 class="text-3xl font-bold">
                                    <?php echo number_format($stats['summary']['total_entries'] ?? 0); ?>
                                </h3>
                            </div>
                            <div class="bg-white/20 p-4 rounded-lg">
                                <i class="fas fa-book text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm mb-1">Institutions</p>
                                <h3 class="text-3xl font-bold">
                                    <?php echo number_format($stats['summary']['unique_institutions'] ?? 0); ?>
                                </h3>
                            </div>
                            <div class="bg-white/20 p-4 rounded-lg">
                                <i class="fas fa-building text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i> Search
                            </label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                                placeholder="Name, email, message..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Institution Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-1"></i> Institution
                            </label>
                            <select name="institution"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Institutions</option>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?php echo htmlspecialchars($inst); ?>" <?php echo $filters['institution'] === $inst ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($inst); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> From Date
                            </label>
                            <input type="date" name="date_from"
                                value="<?php echo htmlspecialchars($filters['date_from']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> To Date
                            </label>
                            <input type="date" name="date_to"
                                value="<?php echo htmlspecialchars($filters['date_to']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Filter Buttons -->
                        <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="guestbook.php"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Guestbook Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Guestbook Entries
                            <span class="text-sm font-normal text-gray-500 ml-2">
                                (<?php echo number_format($totalEntries); ?> entries)
                            </span>
                        </h2>
                    </div>

                    <?php if (count($entries) > 0): ?>
                        <!-- Desktop Table -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Institution</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Message</th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php
                                    $no = $offset + 1;
                                    foreach ($entries as $entry):
                                        ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo $no++; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <i class="far fa-calendar mr-1"></i>
                                                <?php echo htmlspecialchars($entry['formatted_date']); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($entry['name']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    <i class="fas fa-building text-gray-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($entry['institution']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <div>
                                                        <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                                        <a href="mailto:<?php echo htmlspecialchars($entry['email']); ?>"
                                                            class="hover:text-blue-600">
                                                            <?php echo htmlspecialchars($entry['email']); ?>
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-phone text-gray-400 mr-1"></i>
                                                        <?php echo htmlspecialchars($entry['phone']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 max-w-md">
                                                <div class="text-sm text-gray-700 line-clamp-2">
                                                    <?php echo htmlspecialchars($entry['message']); ?>
                                                </div>
                                                <button
                                                    onclick="showMessage(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['name'])); ?>', '<?php echo htmlspecialchars(addslashes($entry['message'])); ?>')"
                                                    class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                                                    Read more...
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <button
                                                    onclick="deleteEntry(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['name'])); ?>')"
                                                    class="text-red-600 hover:text-red-900 transition-colors" title="Delete">
                                                    <i class="fas fa-trash-alt text-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="lg:hidden divide-y divide-gray-200">
                            <?php foreach ($entries as $entry): ?>
                                <div class="p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($entry['name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-building mr-1"></i>
                                                <?php echo htmlspecialchars($entry['institution']); ?>
                                            </p>
                                        </div>
                                        <button
                                            onclick="deleteEntry(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['name'])); ?>')"
                                            class="text-red-600 hover:text-red-900 ml-2">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>

                                    <div class="text-xs text-gray-500 mb-2">
                                        <i class="far fa-calendar mr-1"></i>
                                        <?php echo htmlspecialchars($entry['formatted_date']); ?>
                                    </div>

                                    <div class="text-sm text-gray-600 space-y-1 mb-3">
                                        <div>
                                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($entry['email']); ?>"
                                                class="hover:text-blue-600">
                                                <?php echo htmlspecialchars($entry['email']); ?>
                                            </a>
                                        </div>
                                        <div>
                                            <i class="fas fa-phone text-gray-400 mr-1"></i>
                                            <?php echo htmlspecialchars($entry['phone']); ?>
                                        </div>
                                    </div>

                                    <div class="text-sm text-gray-700 line-clamp-3 mb-2">
                                        <?php echo htmlspecialchars($entry['message']); ?>
                                    </div>

                                    <button
                                        onclick="showMessage(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars(addslashes($entry['name'])); ?>', '<?php echo htmlspecialchars(addslashes($entry['message'])); ?>')"
                                        class="text-blue-600 hover:text-blue-800 text-xs">
                                        Read full message...
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing <?php echo number_format($offset + 1); ?> to
                                    <?php echo number_format(min($offset + $perPage, $totalEntries)); ?> of
                                    <?php echo number_format($totalEntries); ?> entries
                                </div>

                                <div class="flex gap-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>"
                                            class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"
                                            class="px-4 py-2 <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition-colors">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>"
                                            class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="p-12 text-center">
                            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">No guestbook entries found.</p>
                            <?php if (!empty(array_filter($filters))): ?>
                                <a href="guestbook.php" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                    Clear filters
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[80vh] overflow-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle"></h3>
                <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <p id="modalMessage" class="text-gray-700 whitespace-pre-wrap"></p>
            </div>
            <div class="p-6 border-t border-gray-200 flex justify-end bg-gray-50">
                <button onclick="closeMessageModal()"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Show message modal
        function showMessage(id, name, message) {
            document.getElementById('modalTitle').textContent = 'Message from ' + name;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('messageModal').classList.add('flex');
        }

        // Close message modal
        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageModal').classList.remove('flex');
        }

        // Delete entry
        function deleteEntry(id, name) {
            if (confirm('Are you sure you want to delete the entry from "' + name + '"?\nThis action cannot be undone.')) {
                const currentParams = new URLSearchParams(window.location.search);
                currentParams.set('delete', id);
                window.location.href = 'guestbook.php?' + currentParams.toString();
            }
        }

        // Close modal on outside click
        document.getElementById('messageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMessageModal();
            }
        });
    </script>

    <!-- Admin JS for sidebar dropdowns -->
    <script src="assets/js/admin.js"></script>
</body>

</html>