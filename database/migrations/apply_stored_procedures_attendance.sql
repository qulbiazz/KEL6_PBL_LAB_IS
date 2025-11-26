-- ============================================================================
-- MIGRATION: Apply Stored Procedures untuk Sistem Absensi
-- ============================================================================
-- File: apply_stored_procedures_attendance.sql
-- Created: 2025-11-26
-- Description: Migration script untuk menerapkan stored procedures absensi
-- ============================================================================

\echo '================================================'
\echo 'Applying Stored Procedures for Attendance System'
\echo '================================================'
\echo ''

-- Execute the stored procedures file
\echo 'Creating stored procedures...'
\i stored_procedures_attendance.sql

\echo ''
\echo '================================================'
\echo 'Verifying Installation'
\echo '================================================'
\echo ''

-- Verify all procedures are created
SELECT 
    proname as procedure_name,
    pg_get_function_arguments(oid) as parameters
FROM pg_proc
WHERE 
    proname LIKE 'sp_%attendance%' OR
    proname LIKE 'sp_student_check%' OR
    proname LIKE 'sp_delete_attendance' OR
    proname LIKE 'sp_get_attendance%'
ORDER BY proname;

\echo ''
\echo '================================================'
\echo 'Testing Stored Procedures'
\echo '================================================'
\echo ''

-- Test 1: Get attendance stats (should return default values if no data)
\echo 'Test 1: Testing sp_get_attendance_stats...'
DO $$
DECLARE
    v_checkins INTEGER;
    v_checkouts INTEGER;
    v_unique INTEGER;
    v_incomplete INTEGER;
    v_complete INTEGER;
BEGIN
    CALL sp_get_attendance_stats(
        CURRENT_DATE,
        v_checkins,
        v_checkouts,
        v_unique,
        v_incomplete,
        v_complete
    );
    
    RAISE NOTICE 'Date: %, Checkins: %, Checkouts: %, Unique: %, Incomplete: %, Complete: %',
        CURRENT_DATE, v_checkins, v_checkouts, v_unique, v_incomplete, v_complete;
END $$;

\echo ''
\echo 'Test 2: Testing sp_get_student_attendance_summary...'
-- Test 2: Get student summary (using student_id = 1 if exists)
DO $$
DECLARE
    v_total_days INTEGER;
    v_complete INTEGER;
    v_incomplete INTEGER;
    v_avg_duration NUMERIC;
    v_total_hours NUMERIC;
    v_student_id INTEGER;
BEGIN
    -- Get first student
    SELECT id INTO v_student_id FROM students LIMIT 1;
    
    IF v_student_id IS NOT NULL THEN
        CALL sp_get_student_attendance_summary(
            v_student_id,
            v_total_days,
            v_complete,
            v_incomplete,
            v_avg_duration,
            v_total_hours
        );
        
        RAISE NOTICE 'Student ID: %, Total Days: %, Complete: %, Incomplete: %, Avg Duration: % min, Total Hours: %',
            v_student_id, v_total_days, v_complete, v_incomplete, v_avg_duration, v_total_hours;
    ELSE
        RAISE NOTICE 'No students found for testing';
    END IF;
END $$;

\echo ''
\echo '================================================'
\echo 'Migration Summary'
\echo '================================================'
\echo ''
\echo 'Created Stored Procedures:'
\echo '  ✓ sp_student_checkin - Handle student check-in'
\echo '  ✓ sp_student_checkout - Handle student check-out'
\echo '  ✓ sp_delete_attendance - Delete attendance record'
\echo '  ✓ sp_get_attendance_list - Get filtered attendance list'
\echo '  ✓ sp_get_attendance_stats - Get daily statistics'
\echo '  ✓ sp_get_student_attendance_summary - Get student summary'
\echo ''
\echo 'Next Steps:'
\echo '  1. Update PHP AttendanceModel.php to use stored procedures'
\echo '  2. Test all attendance operations through web interface'
\echo '  3. Monitor error logs for any issues'
\echo ''
\echo '================================================'
\echo 'Migration Complete!'
\echo '================================================'
