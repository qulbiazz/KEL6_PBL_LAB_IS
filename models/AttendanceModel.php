<?php
class AttendanceModel {
    private $conn;
    private $table_name = "student_attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Catat absensi mahasiswa (menggunakan stored procedure via wrapper function)
     */
    public function recordAttendance($student_id, $attendance_type, $ip_address = null, $user_agent = null) {
        try {
            // Memanggil wrapper function yang memanggil stored procedure
            if ($attendance_type === 'datang') {
                $query = "SELECT * FROM fn_student_checkin(:student_id, :ip_address, :user_agent)";
            } else {
                $query = "SELECT * FROM fn_student_checkout(:student_id, :ip_address, :user_agent)";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            $stmt->execute();
            
            // Fetch hasil dari wrapper function
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => ($result['p_success'] === 't' || $result['p_success'] === true),
                'message' => $result['p_message'] ?? 'Gagal mencatat absensi.'
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
     * Ambil statistik absensi (menggunakan stored procedure via wrapper function)
     */
    public function getAttendanceStats($date = null) {
        try {
            $date = $date ?: date('Y-m-d');
            
            // Gunakan wrapper function yang memanggil stored procedure
            $query = "SELECT * FROM fn_get_attendance_stats(:date)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            
            $stmt->execute();
            
            // Fetch hasil dari wrapper function
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'attendance_date' => $date,
                'total_checkins' => (int)($result['p_total_checkins'] ?? 0),
                'total_checkouts' => (int)($result['p_total_checkouts'] ?? 0),
                'unique_students' => (int)($result['p_unique_students'] ?? 0),
                'incomplete_count' => (int)($result['p_incomplete_count'] ?? 0),
                'complete_count' => (int)($result['p_complete_count'] ?? 0),
                'avg_duration_minutes' => 0
            ];
            
        } catch(PDOException $e) {
            return [
                'attendance_date' => $date,
                'total_checkins' => 0,
                'total_checkouts' => 0,
                'unique_students' => 0,
                'incomplete_count' => 0,
                'complete_count' => 0,
                'avg_duration_minutes' => 0
            ];
        }
    }

    /**
     * Ambil statistik per mahasiswa (menggunakan stored procedure via wrapper function)
     */
    public function getStudentStats($student_id) {
        try {
            // Gunakan wrapper function yang memanggil stored procedure
            $query = "SELECT * FROM fn_get_student_attendance_summary(:student_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Fetch hasil dari wrapper function
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_days' => (int)($result['p_total_days'] ?? 0),
                'total_complete_days' => (int)($result['p_complete_days'] ?? 0),
                'total_incomplete_days' => (int)($result['p_incomplete_days'] ?? 0),
                'avg_duration_minutes' => (float)($result['p_avg_duration_minutes'] ?? 0),
                'total_duration_hours' => (float)($result['p_total_duration_hours'] ?? 0)
            ];
            
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
     * Hapus data absensi (menggunakan stored procedure via wrapper function)
     */
    public function deleteAttendance($id) {
        try {
            // Gunakan wrapper function yang memanggil stored procedure
            $query = "SELECT * FROM fn_delete_attendance(:id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Fetch hasil dari wrapper function
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['p_success'] === 't' || $result['p_success'] === true);
            
        } catch(PDOException $e) {
            error_log("Error deleting attendance: " . $e->getMessage());
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
