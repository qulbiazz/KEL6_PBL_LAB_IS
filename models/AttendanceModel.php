<?php
class AttendanceModel {
    private $conn;
    private $table_name = "student_attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Catat absensi mahasiswa (menggunakan student_id)
     */
    public function recordAttendance($student_id, $attendance_type, $ip_address = null, $user_agent = null) {
        try {
            // Gunakan function dari database untuk validasi
            if ($attendance_type === 'datang') {
                $check = $this->conn->prepare("SELECT check_student_checkin_today(:student_id)");
                $check->bindParam(':student_id', $student_id, PDO::PARAM_INT);
                $check->execute();
                $can_checkin = $check->fetchColumn();
                
                // PostgreSQL returns 't' for true, 'f' for false
                // Function returns TRUE if CAN check in
                if ($can_checkin === 'f' || $can_checkin === false || $can_checkin === 0) {
                    return [
                        'success' => false,
                        'message' => 'Anda sudah melakukan absensi datang hari ini.'
                    ];
                }
            } else {
                $check = $this->conn->prepare("SELECT check_student_checkout_today(:student_id)");
                $check->bindParam(':student_id', $student_id, PDO::PARAM_INT);
                $check->execute();
                $can_checkout = $check->fetchColumn();
                
                // Function returns TRUE if CAN check out
                if ($can_checkout === 'f' || $can_checkout === false || $can_checkout === 0) {
                    return [
                        'success' => false,
                        'message' => 'Anda harus absen datang terlebih dahulu atau sudah absen pulang hari ini.'
                    ];
                }
            }

            // Insert absensi
            $query = "INSERT INTO " . $this->table_name . " 
                     (student_id, attendance_type, ip_address, user_agent) 
                     VALUES (:student_id, :attendance_type, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
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
     * Ambil semua data absensi dengan filter (menggunakan attendance_daily_view)
     * View ini menggabungkan datang dan pulang dalam 1 row
     */
    public function getAllAttendance($date = null, $student_name = null, $limit = 100, $offset = 0) {
        try {
            $query = "SELECT * FROM attendance_daily_view WHERE 1=1";
            $params = [];

            if ($date) {
                $query .= " AND attendance_date = :date";
                $params[':date'] = $date;
            }

            if ($student_name) {
                $query .= " AND (student_name ILIKE :name OR student_nim ILIKE :name)";
                $params[':name'] = '%' . $student_name . '%';
            }

            $query .= " ORDER BY attendance_date DESC, checkin_time DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Error getting attendance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil recent attendance (10 terbaru)
     */
    public function getRecentAttendance($limit = 10) {
        try {
            $query = "SELECT * FROM attendance_recent_view ORDER BY timestamp DESC LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting recent attendance: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung total absensi dengan filter
     */
    public function countAttendance($date = null, $student_name = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM attendance_daily_view WHERE 1=1";
            $params = [];

            if ($date) {
                $query .= " AND attendance_date = :date";
                $params[':date'] = $date;
            }

            if ($student_name) {
                $query .= " AND (student_name ILIKE :name OR student_nim ILIKE :name)";
                $params[':name'] = '%' . $student_name . '%';
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
     * Ambil statistik absensi (menggunakan stats views)
     */
    public function getAttendanceStats($date = null) {
        try {
            $date = $date ?: date('Y-m-d');
            
            $query = "SELECT * FROM attendance_stats_by_date WHERE attendance_date = :date";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'attendance_date' => $date,
                    'total_checkins' => 0,
                    'total_checkouts' => 0,
                    'unique_students' => 0,
                    'avg_duration_minutes' => 0
                ];
            }
            
            // Map field names from view to expected format
            return [
                'attendance_date' => $result['attendance_date'],
                'total_checkins' => (int)$result['total_check_in'],
                'total_checkouts' => (int)$result['total_check_out'],
                'unique_students' => (int)$result['unique_students'],
                'avg_duration_minutes' => 0 // View doesn't have this field
            ];
            
        } catch(PDOException $e) {
            return [
                'attendance_date' => $date,
                'total_checkins' => 0,
                'total_checkouts' => 0,
                'unique_students' => 0,
                'avg_duration_minutes' => 0
            ];
        }
    }

    /**
     * Ambil statistik per mahasiswa
     */
    public function getStudentStats($student_id) {
        try {
            $query = "SELECT * FROM attendance_stats_by_student WHERE student_id = :student_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'total_days' => 0,
                    'total_complete_days' => 0,
                    'total_incomplete_days' => 0,
                    'avg_duration_minutes' => 0,
                    'total_duration_hours' => 0
                ];
            }
            
            return $result;
        } catch(PDOException $e) {
            error_log("Error getting student stats: " . $e->getMessage());
            return [
                'total_days' => 0,
                'total_complete_days' => 0,
                'total_incomplete_days' => 0,
                'avg_duration_minutes' => 0,
                'total_duration_hours' => 0
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
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Export data absensi ke CSV (dari attendance_daily_view)
     */
    public function exportToCSV($date = null, $student_name = null) {
        $data = $this->getAllAttendance($date, $student_name, 10000, 0);
        
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
        fputcsv($file, ['NIM', 'Nama', 'Tanggal', 'Jam Datang', 'Jam Pulang', 'Durasi (menit)', 'Status']);
        
        // Data
        foreach ($data as $row) {
            fputcsv($file, [
                $row['student_nim'],
                $row['student_name'],
                $row['attendance_date'],
                $row['checkin_time'] ?? '-',
                $row['checkout_time'] ?? '-',
                $row['duration_minutes'] ?? '0',
                $row['status']
            ]);
        }
        
        fclose($file);
        
        return $filename;
    }
}
?>
