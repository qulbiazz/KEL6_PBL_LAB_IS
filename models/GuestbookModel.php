<?php
/**
 * GuestbookModel.php
 * Model untuk mengelola data buku tamu
 * 
 * Created: 2025-11-28
 * Author: Inlet Lab Team
 */

class GuestbookModel {
    private $conn;
    private $table_name = "guestbook";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new guestbook entry
     * 
     * @param array $data - Data guestbook (name, institution, email, phone, message)
     * @param string $ip_address - IP address pengunjung
     * @param string $user_agent - Browser user agent
     * @return array - Result array dengan success status dan message
     */
    public function create($data, $ip_address = null, $user_agent = null) {
        try {
            // Validasi input
            $required_fields = ['name', 'institution', 'email', 'phone', 'message'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => 'Field ' . $field . ' wajib diisi.'
                    ];
                }
            }

            // Validasi email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Format email tidak valid.'
                ];
            }

            // Validasi phone (minimal 10 digit, maksimal 15 digit)
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                return [
                    'success' => false,
                    'message' => 'Nomor telepon harus 10-15 digit.'
                ];
            }

            // Check duplicate entry dalam 5 menit terakhir (spam prevention)
            $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                          WHERE email = :email 
                          AND created_at > NOW() - INTERVAL '5 minutes'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':email', $data['email']);
            $checkStmt->execute();
            $duplicateCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($duplicateCheck['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Anda baru saja mengirim pesan. Mohon tunggu beberapa menit sebelum mengirim lagi.'
                ];
            }

            // Insert data
            $query = "INSERT INTO " . $this->table_name . " 
                     (name, institution, email, phone, message, ip_address, user_agent) 
                     VALUES (:name, :institution, :email, :phone, :message, :ip_address, :user_agent)
                     RETURNING id";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize input
            $name = htmlspecialchars(strip_tags($data['name']));
            $institution = htmlspecialchars(strip_tags($data['institution']));
            $email = htmlspecialchars(strip_tags($data['email']));
            $phone = htmlspecialchars(strip_tags($phone));
            $message = htmlspecialchars(strip_tags($data['message']));
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':institution', $institution);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'message' => 'Terima kasih! Pesan Anda telah berhasil dikirim.',
                    'id' => $result['id']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi.'
            ];
            
        } catch(PDOException $e) {
            error_log("Error creating guestbook entry: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi nanti.'
            ];
        }
    }

    /**
     * Get all guestbook entries with filters and pagination
     * 
     * @param array $filters - Filter options (search, date_from, date_to)
     * @param int $limit - Jumlah data per halaman
     * @param int $offset - Offset untuk pagination
     * @return array - Array of guestbook entries
     */
    public function getAll($filters = [], $limit = 20, $offset = 0) {
        try {
            $query = "SELECT 
                        id,
                        name,
                        institution,
                        email,
                        phone,
                        message,
                        ip_address,
                        created_at,
                        TO_CHAR(created_at, 'DD Mon YYYY, HH24:MI') as formatted_date
                      FROM " . $this->table_name . " 
                      WHERE 1=1";
            
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $query .= " AND (
                    name ILIKE :search OR 
                    institution ILIKE :search OR 
                    email ILIKE :search OR 
                    message ILIKE :search
                )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            // Date from filter
            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            // Date to filter
            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            // Institution filter
            if (!empty($filters['institution'])) {
                $query .= " AND institution ILIKE :institution";
                $params[':institution'] = '%' . $filters['institution'] . '%';
            }

            $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Error getting guestbook entries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total guestbook entries dengan filter
     * 
     * @param array $filters - Filter options
     * @return int - Total count
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
            $params = [];

            // Apply same filters as getAll
            if (!empty($filters['search'])) {
                $query .= " AND (
                    name ILIKE :search OR 
                    institution ILIKE :search OR 
                    email ILIKE :search OR 
                    message ILIKE :search
                )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['date_from'])) {
                $query .= " AND DATE(created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $query .= " AND DATE(created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            if (!empty($filters['institution'])) {
                $query .= " AND institution ILIKE :institution";
                $params[':institution'] = '%' . $filters['institution'] . '%';
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
            
        } catch(PDOException $e) {
            error_log("Error counting guestbook entries: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get guestbook entry by ID
     * 
     * @param int $id - Guestbook entry ID
     * @return array|false - Guestbook entry data or false
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting guestbook entry by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete guestbook entry
     * 
     * @param int $id - Guestbook entry ID
     * @return bool - Success status
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error deleting guestbook entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get guestbook statistics
     * 
     * @return array - Statistics data
     */
    public function getStats() {
        try {
            $query = "SELECT * FROM guestbook_stats";
            $stmt = $this->conn->query($query);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get monthly trend (last 6 months)
            $trendQuery = "SELECT 
                            TO_CHAR(created_at, 'Mon YYYY') as month,
                            COUNT(*) as count
                           FROM " . $this->table_name . "
                           WHERE created_at >= NOW() - INTERVAL '6 months'
                           GROUP BY TO_CHAR(created_at, 'Mon YYYY'), DATE_TRUNC('month', created_at)
                           ORDER BY DATE_TRUNC('month', created_at) DESC";
            $trendStmt = $this->conn->query($trendQuery);
            $trend = $trendStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get top institutions
            $institutionsQuery = "SELECT 
                                    institution,
                                    COUNT(*) as count
                                   FROM " . $this->table_name . "
                                   GROUP BY institution
                                   ORDER BY count DESC
                                   LIMIT 5";
            $institutionsStmt = $this->conn->query($institutionsQuery);
            $topInstitutions = $institutionsStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'summary' => $stats,
                'trend' => $trend,
                'top_institutions' => $topInstitutions
            ];

        } catch(PDOException $e) {
            error_log("Error getting guestbook stats: " . $e->getMessage());
            return [
                'summary' => [
                    'total_entries' => 0,
                    'unique_visitors' => 0,
                    'unique_institutions' => 0
                ],
                'trend' => [],
                'top_institutions' => []
            ];
        }
    }

    /**
     * Export guestbook data to CSV
     * 
     * @param array $filters - Filter options
     * @return string|false - Filename or false on error
     */
    public function exportToCSV($filters = []) {
        try {
            $data = $this->getAll($filters, 10000, 0); // Get all data
            
            if (empty($data)) {
                return false;
            }

            $filename = 'guestbook_export_' . date('Y-m-d_His') . '.csv';
            $filepath = __DIR__ . '/../uploads/exports/' . $filename;
            
            // Create directory if not exists
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0777, true);
            }

            $file = fopen($filepath, 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header CSV
            fputcsv($file, ['No', 'Tanggal', 'Nama', 'Institusi', 'Email', 'Telepon', 'Pesan']);
            
            // Data rows
            $no = 1;
            foreach ($data as $row) {
                fputcsv($file, [
                    $no++,
                    $row['formatted_date'],
                    $row['name'],
                    $row['institution'],
                    $row['email'],
                    $row['phone'],
                    $row['message']
                ]);
            }
            
            fclose($file);
            
            return $filename;
            
        } catch(Exception $e) {
            error_log("Error exporting guestbook to CSV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unique institutions for filter dropdown
     * 
     * @return array - Array of unique institutions
     */
    public function getUniqueInstitutions() {
        try {
            $query = "SELECT DISTINCT institution 
                     FROM " . $this->table_name . " 
                     WHERE institution IS NOT NULL AND institution != ''
                     ORDER BY institution ASC
                     LIMIT 100";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            error_log("Error getting unique institutions: " . $e->getMessage());
            return [];
        }
    }
}
?>
