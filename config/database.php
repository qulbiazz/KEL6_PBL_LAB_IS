<?php
// Load environment variables
require_once __DIR__ . '/env.php';
Env::load();

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Load configuration from environment variables
        $this->host = Env::get('DB_HOST', 'localhost');
        $this->db_name = Env::get('DB_NAME', 'inlet_db');
        $this->username = Env::get('DB_USER', 'postgres');
        $this->password = Env::get('DB_PASSWORD', '');
        $this->port = Env::get('DB_PORT', '5432');
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . 
                ";port=" . $this->port . 
                ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set timezone if needed
            $this->conn->exec("SET timezone = 'Asia/Jakarta'");
            
        } catch(PDOException $exception) {
            // Jika AJAX request, throw exception agar bisa di-catch
            if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                throw new Exception("Koneksi database gagal: " . $exception->getMessage());
            }
            
            // In production, log error instead of displaying
            if (Env::isProduction()) {
                error_log("Database Connection Error: " . $exception->getMessage());
                die("Database connection failed. Please contact administrator.");
            } else {
                echo "Connection error: " . $exception->getMessage();
            }
        }
        
        return $this->conn;
    }
}
?>
