<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/EquipmentModel.php';

$db = new Database();
$conn = $db->getConnection();
$equipmentModel = new EquipmentModel($conn);

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'quantity' => $_POST['quantity'],
                'available_quantity' => $_POST['quantity'], // Initially same as quantity
                'display_order' => $_POST['display_order'] ?? 999
            ];
            
            if ($equipmentModel->createEquipment($data)) {
                $success = 'Alat berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan alat!';
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'quantity' => $_POST['quantity'],
                'available_quantity' => $_POST['available_quantity'],
                'display_order' => $_POST['display_order'] ?? 999,
                'is_active' => isset($_POST['is_active']) ? true : false
            ];
            
            if ($equipmentModel->updateEquipment($id, $data)) {
                $success = 'Alat berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate alat!';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($equipmentModel->deleteEquipment($id)) {
        $success = 'Alat berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus alat!';
    }
}

// Fetch equipment
$equipments = $equipmentModel->getAllEquipment(false);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Alat - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8 mt-12">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Alat</h1>
                <p class="text-gray-600 mt-1">Tambah, edit, dan hapus data alat yang tersedia untuk dipinjam</p>
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
                    <i class="fas fa-plus mr-2"></i>Tambah Alat Baru
                </button>
            </div>

            <!-- Equipment Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Alat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tersedia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urutan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($equipments)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Belum ada data alat</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equipments as $index => $equipment): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($equipment['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600"><?php echo htmlspecialchars($equipment['description'] ?? '-'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $equipment['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $equipment['available_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $equipment['available_quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $equipment['display_order']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $equipment['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $equipment['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick='showEditModal(<?php echo json_encode($equipment); ?>)'
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $equipment['id']; ?>" 
                                            onclick="return confirm('Yakin ingin menghapus alat ini?')"
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

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-200">
                    <?php if (empty($equipments)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada data alat</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($equipments as $index => $equipment): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($equipment['name']); ?></h3>
                                        <p class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($equipment['description'] ?? '-'); ?></p>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $equipment['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $equipment['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-3">
                                    <div>
                                        <span class="text-gray-400">Jumlah:</span> <span class="font-semibold"><?php echo $equipment['quantity']; ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Tersedia:</span> 
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $equipment['available_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $equipment['available_quantity']; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Urutan:</span> <span class="font-semibold"><?php echo $equipment['display_order']; ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end space-x-3">
                                    <button onclick='showEditModal(<?php echo json_encode($equipment); ?>)' class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $equipment['id']; ?>" onclick="return confirm('Yakin ingin menghapus alat ini?')" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="equipmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Tambah Alat</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="equipmentId" name="id">
                <input type="hidden" id="actionType" name="action" value="add">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Alat *</label>
                    <input type="text" id="equipmentName" name="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                    <textarea id="equipmentDescription" name="description" rows="3"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Total *</label>
                    <input type="number" id="equipmentQuantity" name="quantity" min="1" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div id="availableQuantityDiv" class="mb-4 hidden">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Tersedia *</label>
                    <input type="number" id="equipmentAvailable" name="available_quantity" min="0"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Urutan Tampilan</label>
                    <input type="number" id="equipmentOrder" name="display_order" min="1" value="999"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div id="activeCheckDiv" class="mb-4 hidden">
                    <label class="flex items-center">
                        <input type="checkbox" id="equipmentActive" name="is_active" class="mr-2">
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

    <script src="assets/js/admin.js"></script>
    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Alat';
            document.getElementById('actionType').value = 'add';
            document.getElementById('equipmentId').value = '';
            document.getElementById('equipmentName').value = '';
            document.getElementById('equipmentDescription').value = '';
            document.getElementById('equipmentQuantity').value = '1';
            document.getElementById('equipmentOrder').value = '999';
            document.getElementById('availableQuantityDiv').classList.add('hidden');
            document.getElementById('activeCheckDiv').classList.add('hidden');
            document.getElementById('equipmentModal').classList.remove('hidden');
        }

        function showEditModal(equipment) {
            document.getElementById('modalTitle').textContent = 'Edit Alat';
            document.getElementById('actionType').value = 'edit';
            document.getElementById('equipmentId').value = equipment.id;
            document.getElementById('equipmentName').value = equipment.name;
            document.getElementById('equipmentDescription').value = equipment.description || '';
            document.getElementById('equipmentQuantity').value = equipment.quantity;
            document.getElementById('equipmentAvailable').value = equipment.available_quantity;
            document.getElementById('equipmentOrder').value = equipment.display_order;
            document.getElementById('equipmentActive').checked = equipment.is_active;
            document.getElementById('availableQuantityDiv').classList.remove('hidden');
            document.getElementById('activeCheckDiv').classList.remove('hidden');
            document.getElementById('equipmentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('equipmentModal').classList.add('hidden');
        }
    </script>
</body>

</html>
