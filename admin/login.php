<?php
session_start();

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/database.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                $error = 'Database connection failed. Please check configuration.';
            } else {
                $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $error = 'User not found. Please check your username.';
                    // Uncomment for debugging:
                    // $debug_info = "Username entered: $username";
                } else if (!password_verify($password, $user['password'])) {
                    $error = 'Invalid password. Please try again.';
                    // Uncomment for debugging:
                    // $debug_info = "Password hash: " . substr($user['password'], 0, 20) . "...";
                } else {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['full_name'];
                    
                    // Update last login
                    $update = $conn->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $update->execute(['id' => $user['id']]);
                    
                    header('Location: dashboard.php');
                    exit();
                }
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - InLet</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full mb-4">
                <i class="fas fa-user-shield text-2xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">InLet Admin</h1>
            <p class="text-gray-500">Sign in to manage your content</p>
        </div>
        
        <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                <div>
                    <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
                    <?php if (isset($debug_info) && $debug_info): ?>
                    <br><small class="text-red-600"><?php echo htmlspecialchars($debug_info); ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Debug Helper -->
        <!-- <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4 text-sm">
            <p><strong>Need help?</strong> Run <a href="check_admin.php" class="underline font-semibold">check_admin.php</a> to diagnose issues.</p>
        </div>      
         -->
        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="username">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Username
                </label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                    placeholder="Enter your username">
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>Password
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="password-icon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="../index.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Website
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
