<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$admin_id = $_SESSION['admin_id'];
$success = '';
$error = '';

// Get current admin data
$stmt = $db->prepare("SELECT id, username, full_name, last_login FROM admin_users WHERE id = :id");
$stmt->execute(['id' => $admin_id]);
$admin = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    
    // Verify current password
    $stmt = $db->prepare("SELECT password FROM admin_users WHERE id = :id");
    $stmt->execute(['id' => $admin_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Password saat ini salah!';
    } else {
        if ($action === 'change_username') {
            $new_username = trim($_POST['new_username'] ?? '');
            
            if (empty($new_username)) {
                $error = 'Username baru tidak boleh kosong!';
            } else {
                // Check if username already exists
                $check = $db->prepare("SELECT id FROM admin_users WHERE username = :username AND id != :id");
                $check->execute(['username' => $new_username, 'id' => $admin_id]);
                
                if ($check->fetch()) {
                    $error = 'Username sudah digunakan!';
                } else {
                    // Update username
                    $update = $db->prepare("UPDATE admin_users SET username = :username WHERE id = :id");
                    if ($update->execute(['username' => $new_username, 'id' => $admin_id])) {
                        $_SESSION['admin_username'] = $new_username;
                        $admin['username'] = $new_username;
                        $success = 'Username berhasil diubah!';
                    } else {
                        $error = 'Gagal mengubah username!';
                    }
                }
            }
        } elseif ($action === 'change_password') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($new_password)) {
                $error = 'Password baru tidak boleh kosong!';
            } elseif (strlen($new_password) < 6) {
                $error = 'Password minimal 6 karakter!';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru dan konfirmasi tidak cocok!';
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
                
                if ($update->execute(['password' => $hashed_password, 'id' => $admin_id])) {
                    $success = 'Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password!';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php include 'includes/admin_navbar.php'; ?>

    <div class="flex pt-16">
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 ml-0 lg:ml-64 min-h-screen">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Profile Settings</h1>
                <p class="text-gray-500 text-sm mt-1">Kelola informasi akun Anda</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
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

            <!-- Profile Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-blue-600"></i>Informasi Profile
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                        <p class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($admin['username']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</label>
                        <p class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($admin['full_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Last Login</label>
                        <p class="text-lg font-semibold text-gray-800">
                            <?php echo $admin['last_login'] ? date('d M Y, H:i', strtotime($admin['last_login'])) : '-'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button onclick="switchTab('username')" id="tab-username" class="tab-button border-b-2 border-blue-600 py-4 px-1 text-sm font-medium text-blue-600">
                            <i class="fas fa-user mr-2"></i>Ubah Username
                        </button>
                        <button onclick="switchTab('password')" id="tab-password" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-lock mr-2"></i>Ubah Password
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Change Username Form -->
            <div id="content-username" class="tab-content">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4">Ubah Username</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_username">
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Username Saat Ini</label>
                            <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Username Baru <span class="text-red-500">*</span></label>
                            <input type="text" name="new_username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Password Saat Ini <span class="text-red-500">*</span></label>
                            <input type="password" name="current_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Masukkan password untuk konfirmasi</p>
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password Form -->
            <div id="content-password" class="tab-content hidden">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4">Ubah Password</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Password Saat Ini <span class="text-red-500">*</span></label>
                            <input type="password" name="current_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function switchTab(tab) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-600', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tab).classList.remove('hidden');
            
            // Add active state to selected tab
            const activeTab = document.getElementById('tab-' + tab);
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-600', 'text-blue-600');
        }
    </script>
</body>
</html>
