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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ruangan - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8 mt-12">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Ruangan</h1>
                <p class="text-gray-600 mt-1">Tambah, edit, dan hapus data ruangan yang tersedia untuk dipinjam</p>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success)): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Add Button -->
            <div class="mb-6">
                <button onclick="showAddModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Ruangan Baru
                </button>
            </div>

            <!-- Rooms Table -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($rooms)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Belum ada data ruangan</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $index => $room): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($room['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600"><?php echo htmlspecialchars($room['description'] ?? '-'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $room['capacity'] ?? '-'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $room['display_order']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $room['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $room['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick='showEditModal(<?php echo json_encode($room); ?>)'
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $room['id']; ?>" 
                                            onclick="return confirm('Yakin ingin menghapus ruangan ini?')"
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
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="roomModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Ruangan *</label>
                    <input type="text" id="roomName" name="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                    <textarea id="roomDescription" name="description" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Kapasitas (orang)</label>
                    <input type="number" id="roomCapacity" name="capacity" min="1"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Urutan Tampilan</label>
                    <input type="number" id="roomOrder" name="display_order" min="1" value="999"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div id="activeCheckDiv" class="mb-4 hidden">
                    <label class="flex items-center">
                        <input type="checkbox" id="roomActive" name="is_active" class="mr-2">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
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
