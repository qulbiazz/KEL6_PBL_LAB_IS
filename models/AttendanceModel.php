<?php
class AttendanceModel {
    private $conn;
    private $table_name = "student_attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Catat absensi mahasiswa
     */
    public function recordAttendance($nama, $nim, $attendance_type, $ip_address = null, $user_agent = null) {
        try {
            // Cek apakah mahasiswa sudah absen untuk tipe yang sama hari ini
            $check_query = "SELECT id FROM " . $this->table_name . " 
                           WHERE nim = :nim 
                           AND attendance_date = CURRENT_DATE 
                           AND attendance_type = :attendance_type";
            
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':nim', $nim);
            $check_stmt->bindParam(':attendance_type', $attendance_type);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Anda sudah melakukan absensi ' . $attendance_type . ' hari ini.'
                ];
            }

            // Jika absen pulang, cek apakah sudah absen datang
            if ($attendance_type === 'pulang') {
                $check_datang = "SELECT id FROM " . $this->table_name . " 
                                WHERE nim = :nim 
                                AND attendance_date = CURRENT_DATE 
                                AND attendance_type = 'datang'";
                
                $datang_stmt = $this->conn->prepare($check_datang);
                $datang_stmt->bindParam(':nim', $nim);
                $datang_stmt->execute();
                
                if ($datang_stmt->rowCount() == 0) {
                    return [
                        'success' => false,
                        'message' => 'Anda harus absen datang terlebih dahulu sebelum absen pulang.'
                    ];
                }
            }

            // Insert absensi
            $query = "INSERT INTO " . $this->table_name . " 
                     (nama, nim, attendance_type, ip_address, user_agent) 
                     VALUES (:nama, :nim, :attendance_type, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':nim', $nim);
            $stmt->bindParam(':attendance_type', $attendance_type);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Absensi ' . $attendance_type . ' berhasil dicatat.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal mencatat absensi.'
            ];
            
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ambil semua data absensi dengan filter
     */
    public function getAllAttendance($date = null, $nim = null, $type = null, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
            $params = [];

            if ($date) {
                $query .= " AND attendance_date = :date";
                $params[':date'] = $date;
            }

            if ($nim) {
                $query .= " AND nim ILIKE :nim";
                $params[':nim'] = '%' . $nim . '%';
            }

            if ($type) {
                $query .= " AND attendance_type = :type";
                $params[':type'] = $type;
            }

            $query .= " ORDER BY attendance_date DESC, attendance_time DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Hitung total absensi dengan filter
     */
    public function countAttendance($date = null, $nim = null, $type = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
            $params = [];

            if ($date) {
                $query .= " AND attendance_date = :date";
                $params[':date'] = $date;
            }

            if ($nim) {
                $query .= " AND nim ILIKE :nim";
                $params[':nim'] = '%' . $nim . '%';
            }

            if ($type) {
                $query .= " AND attendance_type = :type";
                $params[':type'] = $type;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
            
        } catch(PDOException $e) {
            return 0;
        }
    }

    /**
     * Ambil statistik absensi
     */
    public function getAttendanceStats($date = null) {
        try {
            $date = $date ?: date('Y-m-d');
            
            $query = "SELECT 
                        COUNT(CASE WHEN attendance_type = 'datang' THEN 1 END) as total_datang,
                        COUNT(CASE WHEN attendance_type = 'pulang' THEN 1 END) as total_pulang,
                        COUNT(DISTINCT nim) as unique_students
                      FROM " . $this->table_name . "
                      WHERE attendance_date = :date";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            return [
                'total_datang' => 0,
                'total_pulang' => 0,
                'unique_students' => 0
            ];
        }
    }

    /**
     * Hapus data absensi
     */
    public function deleteAttendance($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Export data absensi ke CSV
     */
    public function exportToCSV($date = null, $nim = null, $type = null) {
        $data = $this->getAllAttendance($date, $nim, $type, 10000, 0);
        
        if (empty($data)) {
            return false;
        }

        $filename = 'attendance_' . date('Y-m-d_His') . '.csv';
        $filepath = __DIR__ . '/../uploads/exports/' . $filename;
        
        // Buat direktori jika belum ada
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }

        $file = fopen($filepath, 'w');
        
        // Header CSV
        fputcsv($file, ['ID', 'Nama', 'NIM', 'Tipe', 'Tanggal', 'Waktu', 'IP Address']);
        
        // Data
        foreach ($data as $row) {
            fputcsv($file, [
                $row['id'],
                $row['nama'],
                $row['nim'],
                $row['attendance_type'],
                $row['attendance_date'],
                $row['attendance_time'],
                $row['ip_address']
            ]);
        }
        
        fclose($file);
        
        return $filename;
    }
}
?>
