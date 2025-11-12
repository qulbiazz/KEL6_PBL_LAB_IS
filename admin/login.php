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
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">InLet Admin</h1>
            <p class="text-gray-600">Login to manage content</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <?php if (isset($debug_info) && $debug_info): ?>
            <br><small><?php echo htmlspecialchars($debug_info); ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Debug Helper -->
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4 text-sm">
            <p><strong>Need help?</strong> Run <a href="check_admin.php" class="underline font-semibold">check_admin.php</a> to diagnose issues.</p>
        </div>
        
        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    <i class="fas fa-user mr-2"></i>Username
                </label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    placeholder="Enter your username">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    placeholder="Enter your password">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Website
            </a>
        </div>
    </div>
</body>
</html>
