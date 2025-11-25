-- ============================================================================
-- ATTENDANCE SYSTEM DATABASE OPTIMIZATION
-- Migrates to relational structure with student master data
-- Creates optimized views for attendance logs
-- ============================================================================

BEGIN;

-- ============================================================================
-- 1. CREATE STUDENTS TABLE (Master Data)
-- ============================================================================

CREATE TABLE IF NOT EXISTS public.students (
    id SERIAL PRIMARY KEY,
    nim VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    program_study VARCHAR(100),
    semester INTEGER,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add indexes for students table
CREATE INDEX IF NOT EXISTS idx_students_nim ON public.students(nim);
CREATE INDEX IF NOT EXISTS idx_students_name ON public.students(name);
CREATE INDEX IF NOT EXISTS idx_students_active ON public.students(is_active);

COMMENT ON TABLE public.students IS 'Master data mahasiswa';
COMMENT ON COLUMN public.students.nim IS 'Nomor Induk Mahasiswa (unique)';
COMMENT ON COLUMN public.students.is_active IS 'Status aktif mahasiswa';

-- ============================================================================
-- 2. BACKUP OLD ATTENDANCE DATA
-- ============================================================================

-- Create backup table
CREATE TABLE IF NOT EXISTS public.student_attendance_backup AS 
SELECT * FROM public.student_attendance;

COMMENT ON TABLE public.student_attendance_backup IS 'Backup data absensi sebelum migrasi';

-- ============================================================================
-- 3. MIGRATE STUDENT DATA FROM ATTENDANCE TO STUDENTS TABLE
-- ============================================================================

-- Insert unique students from attendance data
INSERT INTO public.students (nim, name, is_active)
SELECT DISTINCT 
    nim,
    nama as name,
    true as is_active
FROM public.student_attendance
WHERE NOT EXISTS (
    SELECT 1 FROM public.students WHERE students.nim = student_attendance.nim
)
ORDER BY nim;

-- ============================================================================
-- 4. RESTRUCTURE ATTENDANCE TABLE
-- ============================================================================

-- Drop old constraints and indexes
ALTER TABLE public.student_attendance 
    DROP CONSTRAINT IF EXISTS unique_attendance;

-- Add student_id foreign key column
ALTER TABLE public.student_attendance 
    ADD COLUMN IF NOT EXISTS student_id INTEGER;

-- Migrate data: link attendance records to student_id
UPDATE public.student_attendance sa
SET student_id = s.id
FROM public.students s
WHERE sa.nim = s.nim;

-- Now make student_id NOT NULL and add foreign key
ALTER TABLE public.student_attendance 
    ALTER COLUMN student_id SET NOT NULL;

ALTER TABLE public.student_attendance
    ADD CONSTRAINT fk_attendance_student 
    FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;

-- Make nama and nim nullable (deprecated columns, kept for backward compatibility)
ALTER TABLE public.student_attendance 
    ALTER COLUMN nama DROP NOT NULL,
    ALTER COLUMN nim DROP NOT NULL;

-- Add new unique constraint using student_id
ALTER TABLE public.student_attendance
    ADD CONSTRAINT unique_attendance_student 
    UNIQUE (student_id, attendance_date, attendance_type);

-- Add index for student_id
CREATE INDEX IF NOT EXISTS idx_attendance_student_id ON public.student_attendance(student_id);

COMMENT ON COLUMN public.student_attendance.student_id IS 'Foreign key ke tabel students';
COMMENT ON COLUMN public.student_attendance.nama IS 'DEPRECATED: Gunakan student_id untuk relasi';
COMMENT ON COLUMN public.student_attendance.nim IS 'DEPRECATED: Gunakan student_id untuk relasi';

-- ============================================================================
-- 5. CREATE OPTIMIZED VIEWS
-- ============================================================================

-- View: Attendance Daily Summary (combines datang & pulang in one row)
CREATE OR REPLACE VIEW public.attendance_daily_view AS
SELECT 
    s.id as student_id,
    s.nim,
    s.name as student_name,
    s.program_study,
    a_datang.attendance_date,
    a_datang.attendance_time as check_in_time,
    a_pulang.attendance_time as check_out_time,
    CASE 
        WHEN a_datang.id IS NOT NULL AND a_pulang.id IS NOT NULL THEN 'complete'
        WHEN a_datang.id IS NOT NULL THEN 'check_in_only'
        WHEN a_pulang.id IS NOT NULL THEN 'check_out_only'
        ELSE 'absent'
    END as attendance_status,
    CASE 
        WHEN a_pulang.attendance_time IS NOT NULL AND a_datang.attendance_time IS NOT NULL 
        THEN a_pulang.attendance_time - a_datang.attendance_time
        ELSE NULL
    END as duration,
    a_datang.ip_address as check_in_ip,
    a_pulang.ip_address as check_out_ip,
    COALESCE(a_datang.created_at, a_pulang.created_at) as record_created_at
FROM public.students s
LEFT JOIN public.student_attendance a_datang 
    ON s.id = a_datang.student_id 
    AND a_datang.attendance_type = 'datang'
LEFT JOIN public.student_attendance a_pulang 
    ON s.id = a_pulang.student_id 
    AND a_pulang.attendance_type = 'pulang'
    AND a_datang.attendance_date = a_pulang.attendance_date
WHERE s.is_active = true
    AND (a_datang.id IS NOT NULL OR a_pulang.id IS NOT NULL)
ORDER BY 
    COALESCE(a_datang.attendance_date, a_pulang.attendance_date) DESC, 
    s.name ASC;

COMMENT ON VIEW public.attendance_daily_view IS 
'View optimized untuk menampilkan absensi harian dengan check-in dan check-out dalam satu baris';

-- View: Attendance Statistics by Student
CREATE OR REPLACE VIEW public.attendance_stats_by_student AS
SELECT 
    s.id as student_id,
    s.nim,
    s.name as student_name,
    s.program_study,
    COUNT(DISTINCT CASE WHEN sa.attendance_type = 'datang' THEN sa.attendance_date END) as total_check_in,
    COUNT(DISTINCT CASE WHEN sa.attendance_type = 'pulang' THEN sa.attendance_date END) as total_check_out,
    COUNT(DISTINCT sa.attendance_date) as total_attendance_days,
    MAX(sa.attendance_date) as last_attendance_date,
    MIN(sa.attendance_date) as first_attendance_date
FROM public.students s
LEFT JOIN public.student_attendance sa ON s.id = sa.student_id
WHERE s.is_active = true
GROUP BY s.id, s.nim, s.name, s.program_study
ORDER BY total_attendance_days DESC, s.name ASC;

COMMENT ON VIEW public.attendance_stats_by_student IS 
'View untuk statistik kehadiran per mahasiswa';

-- View: Attendance Statistics by Date
CREATE OR REPLACE VIEW public.attendance_stats_by_date AS
SELECT 
    sa.attendance_date,
    TO_CHAR(sa.attendance_date, 'Day') as day_name,
    COUNT(DISTINCT CASE WHEN sa.attendance_type = 'datang' THEN sa.student_id END) as total_check_in,
    COUNT(DISTINCT CASE WHEN sa.attendance_type = 'pulang' THEN sa.student_id END) as total_check_out,
    COUNT(DISTINCT sa.student_id) as unique_students,
    ARRAY_AGG(DISTINCT s.name ORDER BY s.name) FILTER (WHERE sa.attendance_type = 'datang') as students_checked_in
FROM public.student_attendance sa
JOIN public.students s ON sa.student_id = s.id
GROUP BY sa.attendance_date
ORDER BY sa.attendance_date DESC;

COMMENT ON VIEW public.attendance_stats_by_date IS 
'View untuk statistik kehadiran per tanggal';

-- View: Recent Attendance (Last 100 records with student info)
CREATE OR REPLACE VIEW public.attendance_recent_view AS
SELECT 
    sa.id,
    sa.student_id,
    s.nim,
    s.name as student_name,
    s.program_study,
    sa.attendance_type,
    sa.attendance_date,
    sa.attendance_time,
    sa.ip_address,
    sa.created_at,
    CASE 
        WHEN sa.attendance_type = 'datang' THEN 'Check In'
        WHEN sa.attendance_type = 'pulang' THEN 'Check Out'
    END as attendance_type_label
FROM public.student_attendance sa
JOIN public.students s ON sa.student_id = s.id
WHERE s.is_active = true
ORDER BY sa.created_at DESC
LIMIT 100;

COMMENT ON VIEW public.attendance_recent_view IS 
'View untuk menampilkan 100 record absensi terbaru dengan info mahasiswa';

-- ============================================================================
-- 6. CREATE TRIGGER FOR AUTO-UPDATE TIMESTAMP
-- ============================================================================

-- Function to update timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for students table
DROP TRIGGER IF EXISTS trigger_students_updated_at ON public.students;
CREATE TRIGGER trigger_students_updated_at
    BEFORE UPDATE ON public.students
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- 7. CREATE HELPER FUNCTIONS
-- ============================================================================

-- Function to check if student has checked in today
CREATE OR REPLACE FUNCTION check_student_checkin_today(p_student_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 
        FROM public.student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'datang'
    );
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION check_student_checkin_today IS 
'Mengecek apakah mahasiswa sudah check-in hari ini';

-- Function to check if student has checked out today
CREATE OR REPLACE FUNCTION check_student_checkout_today(p_student_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 
        FROM public.student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'pulang'
    );
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION check_student_checkout_today IS 
'Mengecek apakah mahasiswa sudah check-out hari ini';

-- Function to get student attendance summary for a date range
CREATE OR REPLACE FUNCTION get_student_attendance_summary(
    p_student_id INTEGER,
    p_start_date DATE DEFAULT NULL,
    p_end_date DATE DEFAULT NULL
)
RETURNS TABLE (
    total_days BIGINT,
    complete_days BIGINT,
    checkin_only BIGINT,
    checkout_only BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COUNT(DISTINCT attendance_date) as total_days,
        COUNT(DISTINCT CASE 
            WHEN EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'pulang'
            ) THEN attendance_date 
        END) as complete_days,
        COUNT(DISTINCT CASE 
            WHEN attendance_type = 'datang' 
            AND NOT EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'pulang'
            ) THEN attendance_date 
        END) as checkin_only,
        COUNT(DISTINCT CASE 
            WHEN attendance_type = 'pulang' 
            AND NOT EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'datang'
            ) THEN attendance_date 
        END) as checkout_only
    FROM student_attendance sa
    WHERE student_id = p_student_id
    AND (p_start_date IS NULL OR attendance_date >= p_start_date)
    AND (p_end_date IS NULL OR attendance_date <= p_end_date);
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION get_student_attendance_summary IS 
'Mendapatkan ringkasan kehadiran mahasiswa untuk rentang tanggal tertentu';

-- ============================================================================
-- 8. VERIFICATION QUERIES
-- ============================================================================

-- Count students
DO $$
DECLARE
    student_count INTEGER;
    attendance_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO student_count FROM public.students;
    SELECT COUNT(*) INTO attendance_count FROM public.student_attendance WHERE student_id IS NOT NULL;
    
    RAISE NOTICE 'Migration completed successfully!';
    RAISE NOTICE 'Total students: %', student_count;
    RAISE NOTICE 'Total attendance records with student_id: %', attendance_count;
END $$;

COMMIT;

-- ============================================================================
-- MIGRATION SUMMARY
-- ============================================================================
-- 1. Created students table for master data
-- 2. Migrated unique students from attendance to students table
-- 3. Added student_id foreign key to attendance table
-- 4. Created 4 optimized views for different use cases
-- 5. Created helper functions for attendance checks
-- 6. Created auto-update trigger for students table
-- 7. Backup table created: student_attendance_backup
-- ============================================================================
