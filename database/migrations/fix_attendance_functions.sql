-- Fix attendance validation functions
-- These functions should return TRUE when the action CAN be performed

-- Drop existing functions
DROP FUNCTION IF EXISTS check_student_checkin_today(integer);
DROP FUNCTION IF EXISTS check_student_checkout_today(integer);

-- Recreate with correct logic
-- Returns TRUE if student CAN check in (has NOT checked in today)
CREATE OR REPLACE FUNCTION check_student_checkin_today(p_student_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    -- Return TRUE if NO check-in record exists for today
    RETURN NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'datang'
    );
END;
$$ LANGUAGE plpgsql;

-- Returns TRUE if student CAN check out (has checked in but NOT checked out today)
CREATE OR REPLACE FUNCTION check_student_checkout_today(p_student_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    -- Must have checked in today
    IF NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'datang'
    ) THEN
        RETURN FALSE; -- Cannot check out if haven't checked in
    END IF;
    
    -- Must NOT have checked out yet
    RETURN NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'pulang'
    );
END;
$$ LANGUAGE plpgsql;

-- Test the functions
DO $$
DECLARE
    test_student_id INTEGER := 1;
    can_checkin BOOLEAN;
    can_checkout BOOLEAN;
BEGIN
    -- Test check-in (should return TRUE when no record exists)
    can_checkin := check_student_checkin_today(test_student_id);
    RAISE NOTICE 'Can check in: %', can_checkin;
    
    -- Test check-out (should return FALSE when no check-in exists)
    can_checkout := check_student_checkout_today(test_student_id);
    RAISE NOTICE 'Can check out: %', can_checkout;
END $$;

-- Success message
DO $$
BEGIN
    RAISE NOTICE '';
    RAISE NOTICE '==============================================';
    RAISE NOTICE 'Attendance validation functions fixed!';
    RAISE NOTICE 'Logic corrected:';
    RAISE NOTICE '- check_student_checkin_today: TRUE = CAN check in';
    RAISE NOTICE '- check_student_checkout_today: TRUE = CAN check out';
    RAISE NOTICE '==============================================';
END $$;
