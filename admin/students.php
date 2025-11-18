<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';
require_once '../models/StudentModel.php';

$database = new Database();
$db = $database->getConnection();
$studentModel = new StudentModel($db);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create':
                $data = [
                    'nim' => $_POST['nim'],
                    'name' => $_POST['name'],
                    'email' => $_POST['email'] ?? null,
                    'phone' => $_POST['phone'] ?? null,
                    'program_study' => $_POST['program_study'] ?? null,
                    'semester' => (int)$_POST['semester'],
                    'is_active' => isset($_POST['is_active']) && $_POST['is_active'] === 'true'
                ];

                // Check if NIM already exists
                $existing = $studentModel->getStudentByNim($data['nim']);
                if ($existing) {
                    echo json_encode(['success' => false, 'message' => 'NIM sudah terdaftar']);
                    exit;
                }

                $id = $studentModel->createStudent($data);
                if ($id) {
                    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil ditambahkan', 'id' => $id]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan mahasiswa']);
                }
                break;

            case 'update':
                $id = (int)$_POST['id'];
                $data = [
                    'nim' => $_POST['nim'],
                    'name' => $_POST['name'],
                    'email' => $_POST['email'] ?? null,
                    'phone' => $_POST['phone'] ?? null,
                    'program_study' => $_POST['program_study'] ?? null,
                    'semester' => (int)$_POST['semester'],
                    'is_active' => isset($_POST['is_active']) && $_POST['is_active'] === 'true'
                ];

                // Check if NIM exists for other student
                $existing = $studentModel->getStudentByNim($data['nim']);
                if ($existing && $existing['id'] != $id) {
                    echo json_encode(['success' => false, 'message' => 'NIM sudah digunakan mahasiswa lain']);
                    exit;
                }

                if ($studentModel->updateStudent($id, $data)) {
                    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil diupdate']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate mahasiswa']);
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                if ($studentModel->deleteStudent($id)) {
                    echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil dihapus']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus mahasiswa']);
                }
                break;

            case 'toggle_active':
                $id = (int)$_POST['id'];
                if ($studentModel->toggleActive($id)) {
                    echo json_encode(['success' => true, 'message' => 'Status mahasiswa berhasil diubah']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengubah status']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$program_study = $_GET['program_study'] ?? '';
$is_active = $_GET['is_active'] ?? '';

$filters = [];
if ($search) $filters['search'] = $search;
if ($program_study) $filters['program_study'] = $program_study;
if ($is_active !== '') $filters['is_active'] = $is_active;

// Get data
$students = $studentModel->getAllStudents($filters, 1000, 0);
$stats = $studentModel->getStudentStats();
$programs = $studentModel->getProgramStudies();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mahasiswa - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 pt-20 ml-0 lg:ml-64 min-h-screen">
            <div class="mt-12 mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Mahasiswa</h1>
                <p class="text-gray-600 mt-1">Tambah, edit, dan hapus data mahasiswa</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-4xl font-bold mb-2"><?= $stats['total_students'] ?></h3>
                    <p class="text-blue-100">Total Mahasiswa</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-4xl font-bold mb-2"><?= $stats['active_students'] ?></h3>
                    <p class="text-green-100">Mahasiswa Aktif</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-4xl font-bold mb-2"><?= $stats['inactive_students'] ?></h3>
                    <p class="text-yellow-100">Mahasiswa Non-Aktif</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-lg shadow-lg p-6">
                    <h3 class="text-4xl font-bold mb-2"><?= $stats['total_programs'] ?></h3>
                    <p class="text-cyan-100">Program Studi</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" placeholder="Cari NIM/Nama..." value="<?= htmlspecialchars($search) ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="program_study" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Program Studi</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?= htmlspecialchars($program) ?>" <?= $program_study === $program ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($program) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="true" <?= $is_active === 'true' ? 'selected' : '' ?>>Aktif</option>
                            <option value="false" <?= $is_active === 'false' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Filter</button>
                    </div>
                </form>
            </div>

            <div class="flex justify-start mb-6">
                <button
                    onclick="openAddModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center gap-2"
                    type="button">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Mahasiswa</span>
                </button>
            </div>

            <!-- Students Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block p-6">
                    <div class="overflow-x-auto">
                        <table id="studentsTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($students as $student): ?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['nim']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($student['name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($student['program_study'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $student['semester'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($student['email'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($student['phone'] ?? '-') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($student['is_active'] === 't' || $student['is_active'] === true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= ($student['is_active'] === 't' || $student['is_active'] === true) ? 'Aktif' : 'Non-Aktif' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($student)) ?>)" class="text-blue-600 hover:text-blue-900 mr-3 transition duration-150">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleActive(<?= $student['id'] ?>)" class="text-yellow-600 hover:text-yellow-900 mr-3 transition duration-150">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                            <button onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['name']) ?>')" class="text-red-600 hover:text-red-900 transition duration-150">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-200">
                    <?php if (empty($students)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data mahasiswa</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($student['name']) ?></h3>
                                        <p class="text-xs text-gray-600 mt-1">
                                            <i class="fas fa-id-card mr-1"></i><?= htmlspecialchars($student['nim']) ?>
                                        </p>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($student['is_active'] === 't' || $student['is_active'] === true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ($student['is_active'] === 't' || $student['is_active'] === true) ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </div>

                                <div class="text-xs text-gray-600 space-y-1 mb-3">
                                    <p><i class="fas fa-graduation-cap text-gray-400 mr-2"></i><?= htmlspecialchars($student['program_study'] ?? '-') ?> - Sem <?= $student['semester'] ?></p>
                                    <p><i class="fas fa-envelope text-gray-400 mr-2"></i><?= htmlspecialchars($student['email'] ?? '-') ?></p>
                                    <p><i class="fas fa-phone text-gray-400 mr-2"></i><?= htmlspecialchars($student['phone'] ?? '-') ?></p>
                                </div>

                                <div class="flex items-center justify-end space-x-3">
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($student)) ?>)" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleActive(<?= $student['id'] ?>)" class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['name']) ?>')" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Student Modal -->
    <div id="studentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h5 id="modalTitle" class="text-xl font-semibold text-gray-900">Tambah Mahasiswa</h5>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="studentForm" class="mt-4">
                <input type="hidden" id="studentId" name="id">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" id="formAction" name="action" value="create">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">NIM <span class="text-red-500">*</span></label>
                    <input type="text" id="nim" name="nim" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Telepon</label>
                    <input type="text" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Program Studi</label>
                    <input type="text" id="program_study" name="program_study" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Semester <span class="text-red-500">*</span></label>
                    <input type="number" id="semester" name="semester" min="1" max="14" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="true" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/admin.js"></script>

    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 25
            });
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Mahasiswa';
            document.getElementById('formAction').value = 'create';
            document.getElementById('studentForm').reset();
            document.getElementById('studentId').value = '';
            document.getElementById('is_active').checked = true;
            document.getElementById('studentModal').classList.remove('hidden');
        }

        function openEditModal(student) {
            document.getElementById('modalTitle').textContent = 'Edit Mahasiswa';
            document.getElementById('formAction').value = 'update';
            document.getElementById('studentId').value = student.id;
            document.getElementById('nim').value = student.nim;
            document.getElementById('name').value = student.name;
            document.getElementById('email').value = student.email || '';
            document.getElementById('phone').value = student.phone || '';
            document.getElementById('program_study').value = student.program_study || '';
            document.getElementById('semester').value = student.semester;
            document.getElementById('is_active').checked = student.is_active === 't' || student.is_active === true;
            document.getElementById('studentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('studentModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('studentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
        });

        function toggleActive(id) {
            if (!confirm('Ubah status mahasiswa ini?')) return;

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'toggle_active');
            formData.append('id', id);

            fetch('students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function deleteStudent(id, name) {
            if (!confirm('Hapus mahasiswa ' + name + '?\n\nPeringatan: Data absensi mahasiswa ini juga akan terhapus!')) return;

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>