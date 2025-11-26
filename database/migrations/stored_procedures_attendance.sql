-- ============================================================================
-- STORED PROCEDURES UNTUK SISTEM ABSENSI
-- ============================================================================
-- Created: 2025-11-26
-- Description: Migrasi proses absensi dari PHP ke stored procedures PostgreSQL
-- ============================================================================

-- Drop existing procedures if any
DROP PROCEDURE IF EXISTS sp_student_checkin(INTEGER, BOOLEAN, TEXT, VARCHAR, VARCHAR);
DROP PROCEDURE IF EXISTS sp_student_checkout(INTEGER, BOOLEAN, TEXT, VARCHAR, VARCHAR);
DROP PROCEDURE IF EXISTS sp_delete_attendance(INTEGER, BOOLEAN, TEXT);
DROP PROCEDURE IF EXISTS sp_get_attendance_list(DATE, VARCHAR, INTEGER, INTEGER, REFCURSOR);
DROP PROCEDURE IF EXISTS sp_get_attendance_stats(DATE, INTEGER, INTEGER, INTEGER, INTEGER, INTEGER);
DROP PROCEDURE IF EXISTS sp_get_student_attendance_summary(INTEGER, INTEGER, INTEGER, INTEGER, NUMERIC, NUMERIC);

-- ============================================================================
-- 1. STORED PROCEDURE: Check-in Mahasiswa
-- ============================================================================
-- Purpose: Mencatat absensi datang mahasiswa dengan validasi
-- Parameters:
--   - p_student_id: ID mahasiswa
--   - p_ip_address: IP address (optional)
--   - p_user_agent: User agent browser (optional)
-- Returns: OUT parameters untuk status dan message
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_student_checkin(
    IN p_student_id INTEGER,
    OUT p_success BOOLEAN,
    OUT p_message TEXT,
    IN p_ip_address VARCHAR DEFAULT NULL,
    IN p_user_agent VARCHAR DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_can_checkin BOOLEAN;
    v_student_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Validasi: Cek apakah student exists
    SELECT EXISTS(
        SELECT 1 FROM students WHERE id = p_student_id
    ) INTO v_student_exists;
    
    IF NOT v_student_exists THEN
        p_message := 'Mahasiswa tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Validasi: Gunakan function check_student_checkin_today
    SELECT check_student_checkin_today(p_student_id) INTO v_can_checkin;
    
    IF NOT v_can_checkin THEN
        p_message := 'Anda sudah melakukan absensi datang hari ini.';
        RETURN;
    END IF;
    
    -- Insert absensi datang
    INSERT INTO student_attendance (
        student_id,
        attendance_type,
        ip_address,
        user_agent
    ) VALUES (
        p_student_id,
        'datang',
        p_ip_address,
        p_user_agent
    );
    
    -- Set success
    p_success := TRUE;
    p_message := 'Absensi datang berhasil dicatat.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_student_checkin: %', SQLERRM;
END;
$$;

-- ============================================================================
-- 2. STORED PROCEDURE: Check-out Mahasiswa
-- ============================================================================
-- Purpose: Mencatat absensi pulang mahasiswa dengan validasi
-- Parameters:
--   - p_student_id: ID mahasiswa
--   - p_ip_address: IP address (optional)
--   - p_user_agent: User agent browser (optional)
-- Returns: OUT parameters untuk status dan message
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_student_checkout(
    IN p_student_id INTEGER,
    OUT p_success BOOLEAN,
    OUT p_message TEXT,
    IN p_ip_address VARCHAR DEFAULT NULL,
    IN p_user_agent VARCHAR DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_can_checkout BOOLEAN;
    v_student_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Validasi: Cek apakah student exists
    SELECT EXISTS(
        SELECT 1 FROM students WHERE id = p_student_id
    ) INTO v_student_exists;
    
    IF NOT v_student_exists THEN
        p_message := 'Mahasiswa tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Validasi: Gunakan function check_student_checkout_today
    SELECT check_student_checkout_today(p_student_id) INTO v_can_checkout;
    
    IF NOT v_can_checkout THEN
        p_message := 'Anda harus absen datang terlebih dahulu atau sudah absen pulang hari ini.';
        RETURN;
    END IF;
    
    -- Insert absensi pulang
    INSERT INTO student_attendance (
        student_id,
        attendance_type,
        ip_address,
        user_agent
    ) VALUES (
        p_student_id,
        'pulang',
        p_ip_address,
        p_user_agent
    );
    
    -- Set success
    p_success := TRUE;
    p_message := 'Absensi pulang berhasil dicatat.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_student_checkout: %', SQLERRM;
END;
$$;

-- ============================================================================
-- 3. STORED PROCEDURE: Delete Attendance Record
-- ============================================================================
-- Purpose: Menghapus record absensi (admin only)
-- Parameters:
--   - p_attendance_id: ID record absensi yang akan dihapus
-- Returns: OUT parameters untuk status dan message
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_delete_attendance(
    IN p_attendance_id INTEGER,
    OUT p_success BOOLEAN,
    OUT p_message TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_record_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Cek apakah record exists
    SELECT EXISTS(
        SELECT 1 FROM student_attendance WHERE id = p_attendance_id
    ) INTO v_record_exists;
    
    IF NOT v_record_exists THEN
        p_message := 'Data absensi tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Delete record
    DELETE FROM student_attendance WHERE id = p_attendance_id;
    
    -- Set success
    p_success := TRUE;
    p_message := 'Data absensi berhasil dihapus.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_delete_attendance: %', SQLERRM;
END;
$$;

-- ============================================================================
-- 4. STORED PROCEDURE: Get Attendance List with Filters
-- ============================================================================
-- Purpose: Mengambil daftar absensi dengan filter dan pagination
-- Parameters:
--   - p_date: Filter tanggal (optional)
--   - p_student_name: Filter nama/NIM mahasiswa (optional)
--   - p_limit: Jumlah record per page
--   - p_offset: Offset untuk pagination
-- Returns: Result set dari attendance_daily_view
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_get_attendance_list(
    IN p_date DATE DEFAULT NULL,
    IN p_student_name VARCHAR DEFAULT NULL,
    IN p_limit INTEGER DEFAULT 100,
    IN p_offset INTEGER DEFAULT 0,
    INOUT result_cursor REFCURSOR DEFAULT 'attendance_cursor'
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Open cursor dengan query berdasarkan filter
    OPEN result_cursor FOR
        SELECT 
            student_id,
            student_nim,
            student_name,
            attendance_date,
            checkin_time,
            checkout_time,
            duration_minutes,
            status
        FROM attendance_daily_view
        WHERE 
            (p_date IS NULL OR attendance_date = p_date)
            AND (p_student_name IS NULL OR 
                 student_name ILIKE '%' || p_student_name || '%' OR 
                 student_nim ILIKE '%' || p_student_name || '%')
        ORDER BY attendance_date DESC, checkin_time DESC
        LIMIT p_limit
        OFFSET p_offset;
END;
$$;

-- ============================================================================
-- 5. STORED PROCEDURE: Get Attendance Statistics by Date
-- ============================================================================
-- Purpose: Mengambil statistik absensi untuk tanggal tertentu
-- Parameters:
--   - p_date: Tanggal untuk statistik (default: today)
-- Returns: OUT parameters dengan statistik
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_get_attendance_stats(
    IN p_date DATE,
    OUT p_total_checkins INTEGER,
    OUT p_total_checkouts INTEGER,
    OUT p_unique_students INTEGER,
    OUT p_incomplete_count INTEGER,
    OUT p_complete_count INTEGER
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Set default date if null
    IF p_date IS NULL THEN
        p_date := CURRENT_DATE;
    END IF;
    -- Get statistics dari view attendance_stats_by_date
    SELECT 
        COALESCE(total_check_in, 0),
        COALESCE(total_check_out, 0),
        COALESCE(unique_students, 0),
        0, -- incomplete count (calculate separately)
        0  -- complete count (calculate separately)
    INTO 
        p_total_checkins,
        p_total_checkouts,
        p_unique_students,
        p_incomplete_count,
        p_complete_count
    FROM attendance_stats_by_date
    WHERE attendance_date = p_date;
    
    -- If no data found, set defaults
    IF NOT FOUND THEN
        p_total_checkins := 0;
        p_total_checkouts := 0;
        p_unique_students := 0;
        p_incomplete_count := 0;
        p_complete_count := 0;
    END IF;
    
    -- Calculate complete and incomplete counts
    SELECT 
        COUNT(*) FILTER (WHERE status = 'Lengkap'),
        COUNT(*) FILTER (WHERE status = 'Belum Pulang')
    INTO
        p_complete_count,
        p_incomplete_count
    FROM attendance_daily_view
    WHERE attendance_date = p_date;
    
EXCEPTION
    WHEN OTHERS THEN
        -- Set default values on error
        p_total_checkins := 0;
        p_total_checkouts := 0;
        p_unique_students := 0;
        p_incomplete_count := 0;
        p_complete_count := 0;
        RAISE NOTICE 'Error in sp_get_attendance_stats: %', SQLERRM;
END;
$$;

-- ============================================================================
-- 6. STORED PROCEDURE: Get Student Attendance Summary
-- ============================================================================
-- Purpose: Mengambil ringkasan absensi untuk mahasiswa tertentu
-- Parameters:
--   - p_student_id: ID mahasiswa
-- Returns: OUT parameters dengan statistik mahasiswa
-- ============================================================================

CREATE OR REPLACE PROCEDURE sp_get_student_attendance_summary(
    IN p_student_id INTEGER,
    OUT p_total_days INTEGER,
    OUT p_complete_days INTEGER,
    OUT p_incomplete_days INTEGER,
    OUT p_avg_duration_minutes NUMERIC,
    OUT p_total_duration_hours NUMERIC
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Get statistics dari view attendance_stats_by_student
    SELECT 
        COALESCE(total_days, 0),
        COALESCE(total_complete_days, 0),
        COALESCE(total_incomplete_days, 0),
        COALESCE(avg_duration_minutes, 0),
        COALESCE(total_duration_hours, 0)
    INTO 
        p_total_days,
        p_complete_days,
        p_incomplete_days,
        p_avg_duration_minutes,
        p_total_duration_hours
    FROM attendance_stats_by_student
    WHERE student_id = p_student_id;
    
    -- If no data found, set defaults
    IF NOT FOUND THEN
        p_total_days := 0;
        p_complete_days := 0;
        p_incomplete_days := 0;
        p_avg_duration_minutes := 0;
        p_total_duration_hours := 0;
    END IF;
    
EXCEPTION
    WHEN OTHERS THEN
        -- Set default values on error
        p_total_days := 0;
        p_complete_days := 0;
        p_incomplete_days := 0;
        p_avg_duration_minutes := 0;
        p_total_duration_hours := 0;
        RAISE NOTICE 'Error in sp_get_student_attendance_summary: %', SQLERRM;
END;
$$;

-- ============================================================================
-- WRAPPER FUNCTIONS untuk PHP
-- ============================================================================
-- Functions ini membungkus procedures agar bisa dipanggil dengan SELECT
-- karena PHP PDO tidak support CALL procedure dengan OUT parameters
-- ============================================================================

-- Wrapper function untuk sp_student_checkin
CREATE OR REPLACE FUNCTION fn_student_checkin(
    p_student_id INTEGER,
    p_ip_address VARCHAR DEFAULT NULL,
    p_user_agent VARCHAR DEFAULT NULL
)
RETURNS TABLE (
    p_success BOOLEAN,
    p_message TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_student_checkin(
        p_student_id,
        v_success,
        v_message,
        p_ip_address,
        p_user_agent
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;

-- Wrapper function untuk sp_student_checkout
CREATE OR REPLACE FUNCTION fn_student_checkout(
    p_student_id INTEGER,
    p_ip_address VARCHAR DEFAULT NULL,
    p_user_agent VARCHAR DEFAULT NULL
)
RETURNS TABLE (
    p_success BOOLEAN,
    p_message TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_student_checkout(
        p_student_id,
        v_success,
        v_message,
        p_ip_address,
        p_user_agent
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;

-- Wrapper function untuk sp_delete_attendance
CREATE OR REPLACE FUNCTION fn_delete_attendance(
    p_attendance_id INTEGER
)
RETURNS TABLE (
    p_success BOOLEAN,
    p_message TEXT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_delete_attendance(
        p_attendance_id,
        v_success,
        v_message
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;

-- Wrapper function untuk sp_get_attendance_stats
CREATE OR REPLACE FUNCTION fn_get_attendance_stats(
    p_date DATE
)
RETURNS TABLE (
    p_total_checkins INTEGER,
    p_total_checkouts INTEGER,
    p_unique_students INTEGER,
    p_incomplete_count INTEGER,
    p_complete_count INTEGER
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_checkins INTEGER;
    v_checkouts INTEGER;
    v_unique INTEGER;
    v_incomplete INTEGER;
    v_complete INTEGER;
BEGIN
    -- Call stored procedure
    CALL sp_get_attendance_stats(
        p_date,
        v_checkins,
        v_checkouts,
        v_unique,
        v_incomplete,
        v_complete
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_checkins, v_checkouts, v_unique, v_incomplete, v_complete;
END;
$$;

-- Wrapper function untuk sp_get_student_attendance_summary
CREATE OR REPLACE FUNCTION fn_get_student_attendance_summary(
    p_student_id INTEGER
)
RETURNS TABLE (
    p_total_days INTEGER,
    p_complete_days INTEGER,
    p_incomplete_days INTEGER,
    p_avg_duration_minutes NUMERIC,
    p_total_duration_hours NUMERIC
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_total_days INTEGER;
    v_complete INTEGER;
    v_incomplete INTEGER;
    v_avg_duration NUMERIC;
    v_total_hours NUMERIC;
BEGIN
    -- Call stored procedure
    CALL sp_get_student_attendance_summary(
        p_student_id,
        v_total_days,
        v_complete,
        v_incomplete,
        v_avg_duration,
        v_total_hours
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_total_days, v_complete, v_incomplete, v_avg_duration, v_total_hours;
END;
$$;

-- ============================================================================
-- GRANT PERMISSIONS
-- ============================================================================
-- Grant execute permissions to web application user

-- Procedures (untuk direct call jika diperlukan)
GRANT EXECUTE ON PROCEDURE sp_student_checkin TO inlet_user;
GRANT EXECUTE ON PROCEDURE sp_student_checkout TO inlet_user;
GRANT EXECUTE ON PROCEDURE sp_delete_attendance TO inlet_user;
GRANT EXECUTE ON PROCEDURE sp_get_attendance_list TO inlet_user;
GRANT EXECUTE ON PROCEDURE sp_get_attendance_stats TO inlet_user;
GRANT EXECUTE ON PROCEDURE sp_get_student_attendance_summary TO inlet_user;

-- Wrapper Functions (untuk PHP call)
GRANT EXECUTE ON FUNCTION fn_student_checkin TO inlet_user;
GRANT EXECUTE ON FUNCTION fn_student_checkout TO inlet_user;
GRANT EXECUTE ON FUNCTION fn_delete_attendance TO inlet_user;
GRANT EXECUTE ON FUNCTION fn_get_attendance_stats TO inlet_user;
GRANT EXECUTE ON FUNCTION fn_get_student_attendance_summary TO inlet_user;

-- ============================================================================
-- TESTING QUERIES (Comment out in production)
-- ============================================================================

-- Test checkin
-- CALL sp_student_checkin(1, '127.0.0.1', 'Mozilla/5.0', NULL, NULL);

-- Test checkout
-- CALL sp_student_checkout(1, '127.0.0.1', 'Mozilla/5.0', NULL, NULL);

-- Test get stats
-- CALL sp_get_attendance_stats(CURRENT_DATE, NULL, NULL, NULL, NULL, NULL);

-- Test get student summary
-- CALL sp_get_student_attendance_summary(1, NULL, NULL, NULL, NULL, NULL);

-- Test delete
-- CALL sp_delete_attendance(1, NULL, NULL);

-- ============================================================================
-- CHANGELOG
-- ============================================================================
-- 2025-11-26: Initial creation of stored procedures for attendance system
--             - sp_student_checkin: Handle check-in with validation
--             - sp_student_checkout: Handle check-out with validation
--             - sp_delete_attendance: Delete attendance record
--             - sp_get_attendance_list: Get filtered attendance list
--             - sp_get_attendance_stats: Get daily statistics
--             - sp_get_student_attendance_summary: Get student summary
-- ============================================================================
