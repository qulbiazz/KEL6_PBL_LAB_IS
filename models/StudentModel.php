<?php

/**
 * Student Model - Master Data Mahasiswa
 * Handles CRUD operations for students table
 */
class StudentModel
{
    private $conn;
    private $table = "students";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ========================================================================
    // CREATE
    // ========================================================================

    /**
     * Create new student
     */
    public function createStudent($data)
    {
        try {
            $query = "INSERT INTO $this->table 
                      (nim, name, email, phone, program_study, semester, is_active)
                      VALUES (:nim, :name, :email, :phone, :program_study, :semester, :is_active)
                      RETURNING id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nim', $data['nim']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':program_study', $data['program_study']);
            $stmt->bindParam(':semester', $data['semester'], PDO::PARAM_INT);
            
            $is_active = $data['is_active'] ?? true;
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error creating student: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // READ
    // ========================================================================

    /**
     * Get all students with pagination and filters
     */
    public function getAllStudents($filters = [], $limit = 50, $offset = 0)
    {
        try {
            $query = "SELECT * FROM $this->table WHERE 1=1";
            $params = [];

            if (isset($filters['is_active']) && $filters['is_active'] !== '') {
                $isActiveValue = ($filters['is_active'] === 'true') ? 't' : 'f';
                $query .= " AND is_active = :is_active";
                $params[':is_active'] = $isActiveValue;
            }

            if (!empty($filters['search'])) {
                $query .= " AND (name ILIKE :search OR nim ILIKE :search OR program_study ILIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['program_study'])) {
                $query .= " AND program_study = :program_study";
                $params[':program_study'] = $filters['program_study'];
            }

            $query .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting students: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active students for dropdown
     */
    public function getActiveStudents()
    {
        try {
            $query = "SELECT id, nim, name, program_study 
                      FROM $this->table 
                      WHERE is_active = true 
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active students: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student by ID
     */
    public function getStudentById($id)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student by NIM
     */
    public function getStudentByNim($nim)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $this->table WHERE nim = :nim LIMIT 1");
            $stmt->bindParam(':nim', $nim);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student by NIM: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count students
     */
    public function countStudents($filters = [])
    {
        try {
            $query = "SELECT COUNT(*) as total FROM $this->table WHERE 1=1";
            $params = [];

            if (!empty($filters['is_active'])) {
                $query .= " AND is_active = :is_active";
                $params[':is_active'] = $filters['is_active'] === 'true';
            }

            if (!empty($filters['search'])) {
                $query .= " AND (name ILIKE :search OR nim ILIKE :search OR program_study ILIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['program_study'])) {
                $query .= " AND program_study = :program_study";
                $params[':program_study'] = $filters['program_study'];
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Error counting students: " . $e->getMessage());
            return 0;
        }
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    /**
     * Update student
     */
    public function updateStudent($id, $data)
    {
        try {
            $query = "UPDATE $this->table SET 
                        nim = :nim,
                        name = :name,
                        email = :email,
                        phone = :phone,
                        program_study = :program_study,
                        semester = :semester,
                        is_active = :is_active,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nim', $data['nim']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':program_study', $data['program_study']);
            $stmt->bindParam(':semester', $data['semester'], PDO::PARAM_INT);
            $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating student: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle student active status
     */
    public function toggleActive($id)
    {
        try {
            $query = "UPDATE $this->table 
                      SET is_active = NOT is_active, 
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error toggling student status: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // DELETE
    // ========================================================================

    /**
     * Delete student (will cascade delete attendance records)
     */
    public function deleteStudent($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM $this->table WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting student: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // STATISTICS
    // ========================================================================

    /**
     * Get student statistics
     */
    public function getStudentStats()
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_students,
                        COUNT(*) FILTER (WHERE is_active = true) as active_students,
                        COUNT(*) FILTER (WHERE is_active = false) as inactive_students,
                        COUNT(DISTINCT program_study) as total_programs
                      FROM $this->table";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student stats: " . $e->getMessage());
            return [
                'total_students' => 0,
                'active_students' => 0,
                'inactive_students' => 0,
                'total_programs' => 0
            ];
        }
    }

    /**
     * Get program study list
     */
    public function getProgramStudies()
    {
        try {
            $query = "SELECT DISTINCT program_study 
                      FROM $this->table 
                      WHERE program_study IS NOT NULL 
                      ORDER BY program_study ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error getting program studies: " . $e->getMessage());
            return [];
        }
    }
}
