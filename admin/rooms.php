<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/RoomModel.php';

$db = new Database();
$conn = $db->getConnection();
$roomModel = new RoomModel($conn);

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'capacity' => $_POST['capacity'] ?? null,
                'display_order' => $_POST['display_order'] ?? 999
            ];
            
            if ($roomModel->createRoom($data)) {
                $success = 'Ruangan berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan ruangan!';
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'capacity' => $_POST['capacity'] ?? null,
                'display_order' => $_POST['display_order'] ?? 999,
                'is_active' => isset($_POST['is_active']) ? true : false
            ];
            
            if ($roomModel->updateRoom($id, $data)) {
                $success = 'Ruangan berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate ruangan!';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($roomModel->deleteRoom($id)) {
        $success = 'Ruangan berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus ruangan!';
    }
}

// Fetch rooms
$rooms = $roomModel->getAllRooms(false);
$total_rooms = count($rooms);
$active_rooms = count(array_filter($rooms, fn($r) => $r['is_active']));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ruangan - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .room-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Ruangan</h1>
                    <p class="text-gray-500 text-sm mt-1">Tambah, edit, dan hapus data ruangan yang tersedia untuk dipinjam</p>
                </div>
                <button onclick="showAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition shadow-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah Ruangan
                </button>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-xl mr-3"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-door-open text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Ruangan</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $total_rooms; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Ruangan Aktif</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $active_rooms; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Daftar Ruangan (<?php echo $total_rooms; ?>)
                </h2>
                
                <?php if (empty($rooms)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Belum ada data ruangan</p>
                        <p class="text-gray-400 text-sm mt-2">Klik tombol "Tambah Ruangan" untuk menambah data</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-card bg-white rounded-xl p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($room['name']); ?></h3>
                                        <p class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars($room['description'] ?? '-'); ?></p>
                                    </div>
                                    <span class="ml-3 px-3 py-1 rounded-full text-xs font-semibold <?php echo $room['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo $room['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-gray-100">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Kapasitas</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            <i class="fas fa-users text-blue-600 mr-1"></i>
                                            <?php echo $room['capacity'] ?? '-'; ?>
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Urutan</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            <i class="fas fa-sort text-purple-600 mr-1"></i>
                                            #<?php echo $room['display_order']; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                                    <button onclick='showEditModal(<?php echo json_encode($room); ?>)' class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit mr-2"></i> Edit Ruangan
                                    </button>
                                    <a href="?delete=<?php echo $room['id']; ?>" onclick="return confirm('Yakin ingin menghapus ruangan ini?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="roomModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Tambah Ruangan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="roomId" name="id">
                <input type="hidden" id="actionType" name="action" value="add">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan *</label>
                    <input type="text" id="roomName" name="name" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="roomDescription" name="description" rows="3"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (orang)</label>
                    <input type="number" id="roomCapacity" name="capacity" min="1"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Tampilan</label>
                    <input type="number" id="roomOrder" name="display_order" min="1" value="999"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div id="activeCheckDiv" class="mb-4 hidden">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="roomActive" name="is_active" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Aktif (tersedia untuk dipinjam)</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2.5 px-6 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg transition-colors shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Ruangan';
            document.getElementById('actionType').value = 'add';
            document.getElementById('roomId').value = '';
            document.getElementById('roomName').value = '';
            document.getElementById('roomDescription').value = '';
            document.getElementById('roomCapacity').value = '';
            document.getElementById('roomOrder').value = '999';
            document.getElementById('activeCheckDiv').classList.add('hidden');
            document.getElementById('roomModal').classList.remove('hidden');
        }

        function showEditModal(room) {
            document.getElementById('modalTitle').textContent = 'Edit Ruangan';
            document.getElementById('actionType').value = 'edit';
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.name;
            document.getElementById('roomDescription').value = room.description || '';
            document.getElementById('roomCapacity').value = room.capacity || '';
            document.getElementById('roomOrder').value = room.display_order;
            document.getElementById('roomActive').checked = room.is_active;
            document.getElementById('activeCheckDiv').classList.remove('hidden');
            document.getElementById('roomModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('roomModal').classList.add('hidden');
        }
    </script>
</body>

</html>
