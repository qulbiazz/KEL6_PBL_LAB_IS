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
            // Check if equipment name already exists
            $existingEquipment = $equipmentModel->getEquipmentByName($_POST['name']);
            if ($existingEquipment) {
                $error = 'Alat dengan nama tersebut sudah ada!';
            } else {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'quantity' => $_POST['quantity'],
                    'available_quantity' => $_POST['quantity'], // Initially same as quantity
                    'display_order' => 999
                ];
                
                if ($equipmentModel->createEquipment($data)) {
                    $success = 'Alat berhasil ditambahkan!';
                } else {
                    $error = 'Gagal menambahkan alat!';
                }
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            
            // Check if equipment name already exists (except current equipment)
            $existingEquipment = $equipmentModel->getEquipmentByName($_POST['name']);
            if ($existingEquipment && $existingEquipment['id'] != $id) {
                $error = 'Alat dengan nama tersebut sudah ada!';
            } else {
                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'quantity' => $_POST['quantity'],
                    'available_quantity' => $_POST['available_quantity'],
                    'display_order' => 999,
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
}

// Handle delete (hard delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($equipmentModel->permanentDeleteEquipment($id)) {
        $success = 'Alat berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus alat!';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Fetch equipment with pagination
$all_equipments = $equipmentModel->getAllEquipment(false);
$total_items = count($all_equipments);
$total_pages = ceil($total_items / $per_page);
$equipments = array_slice($all_equipments, $offset, $per_page);

// Statistics
$total_quantity = array_sum(array_map('intval', array_column($all_equipments, 'quantity')));
$total_available = array_sum(array_map('intval', array_column($all_equipments, 'available_quantity')));
$total_borrowed = $total_quantity - $total_available;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Alat - InLet Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .equipment-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .equipment-card:hover {
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Alat</h1>
                    <p class="text-gray-500 text-sm mt-1">Tambah, edit, dan hapus data alat yang tersedia untuk dipinjam</p>
                </div>
                <button onclick="showAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-lg transition shadow-sm">
                    <i class="fas fa-plus mr-2"></i>Tambah Alat
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-tools text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Alat</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $total_quantity; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tersedia</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $total_available; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-hand-holding text-2xl text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $total_borrowed; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        Daftar Alat (<?php echo $total_items; ?>)
                    </h2>
                </div>
                
                <?php if (empty($equipments)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Belum ada data alat</p>
                        <p class="text-gray-400 text-sm mt-2">Klik tombol "Tambah Alat" untuk menambah data</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        <?php foreach ($equipments as $equipment): ?>
                            <div class="equipment-card bg-white rounded-xl p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($equipment['name']); ?></h3>
                                        <p class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars($equipment['description'] ?? '-'); ?></p>
                                    </div>
                                    <span class="ml-3 px-3 py-1 rounded-full text-xs font-semibold <?php echo $equipment['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo $equipment['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-gray-100">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Total</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            <i class="fas fa-box text-blue-600 mr-1"></i>
                                            <?php echo (int)$equipment['quantity']; ?>
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Tersedia</p>
                                        <p class="text-lg font-bold <?php echo $equipment['available_quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            <?php echo (int)$equipment['available_quantity']; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 pt-4 border-t border-gray-100">
                                    <button onclick='showEditModal(<?php echo json_encode($equipment); ?>)' class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit mr-2"></i> Edit Alat
                                    </button>
                                    <a href="?delete=<?php echo $equipment['id']; ?>&page=<?php echo $page; ?>" onclick="return confirm('Yakin ingin menghapus alat ini?')" class="w-full bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-trash mr-2"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center items-center gap-2 pt-6 border-t border-gray-200">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <?php endif; ?>
                        
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="equipmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Alat *</label>
                    <input type="text" id="equipmentName" name="name" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="equipmentDescription" name="description" rows="3"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Total *</label>
                    <input type="number" id="equipmentQuantity" name="quantity" min="1" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div id="availableQuantityDiv" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Tersedia *</label>
                    <input type="number" id="equipmentAvailable" name="available_quantity" min="0"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div id="activeCheckDiv" class="mb-4 hidden">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="equipmentActive" name="is_active" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
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
            document.getElementById('modalTitle').textContent = 'Tambah Alat';
            document.getElementById('actionType').value = 'add';
            document.getElementById('equipmentId').value = '';
            document.getElementById('equipmentName').value = '';
            document.getElementById('equipmentDescription').value = '';
            document.getElementById('equipmentQuantity').value = '1';
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
