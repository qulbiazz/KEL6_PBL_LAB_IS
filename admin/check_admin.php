<?php
// Script untuk mengecek dan memperbaiki admin user
require_once '../config/database.php';

echo "<h2>Database Connection Test & Admin User Check</h2>";
echo "<hr>";

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection FAILED!</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    exit();
}

echo "<p style='color: green;'>✅ Database connection successful!</p>";
echo "<hr>";

// Check if admin_users table exists
try {
    $check_table = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'admin_users'
    )");
    $table_exists = $check_table->fetchColumn();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Table 'admin_users' does NOT exist!</p>";
        echo "<p>Please run the init_db.sql script first.</p>";
        exit();
    }
    
    echo "<p style='color: green;'>✅ Table 'admin_users' exists!</p>";
    echo "<hr>";
    
    // Check admin user
    $stmt = $conn->query("SELECT * FROM admin_users WHERE username = 'admin'");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "<p style='color: orange;'>⚠️ Admin user NOT found! Creating...</p>";
        
        // Create admin user
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO admin_users (username, password, email, full_name) VALUES (:username, :password, :email, :full_name)");
        $insert->execute([
            'username' => 'admin',
            'password' => $password_hash,
            'email' => 'admin@inlet.com',
            'full_name' => 'Administrator'
        ]);
        
        echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color: green;'>✅ Admin user exists!</p>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Full Name:</strong> " . htmlspecialchars($admin['full_name']) . "</p>";
        echo "<hr>";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $admin['password'])) {
            echo "<p style='color: green;'>✅ Password verification TEST: SUCCESS</p>";
            echo "<p>The password 'admin123' is correct for user 'admin'</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification TEST: FAILED</p>";
            echo "<p>Password hash in database: " . substr($admin['password'], 0, 50) . "...</p>";
            echo "<p>Resetting password to 'admin123'...</p>";
            
            // Reset password
            $new_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE admin_users SET password = :password WHERE username = 'admin'");
            $update->execute(['password' => $new_password_hash]);
            
            echo "<p style='color: green;'>✅ Password has been reset!</p>";
            echo "<p><strong>New Password:</strong> admin123</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>All Users in Database:</h3>";
    $all_users = $conn->query("SELECT id, username, email, full_name, created_at, last_login FROM admin_users");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th><th>Last Login</th></tr>";
    while ($user = $all_users->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($user['last_login'] ?? 'Never') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>← Back to Login</a></p>";
?>
