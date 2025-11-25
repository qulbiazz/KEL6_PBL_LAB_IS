-- Fix attendance_daily_view to match PHP expectations
DROP VIEW IF EXISTS attendance_daily_view CASCADE;

CREATE VIEW attendance_daily_view AS
SELECT 
    s.id as student_id,
    s.nim as student_nim,
    s.name as student_name,
    s.program_study,
    COALESCE(datang.attendance_date, pulang.attendance_date) as attendance_date,
    datang.attendance_time as checkin_time,
    pulang.attendance_time as checkout_time,
    CASE 
        WHEN datang.id IS NOT NULL AND pulang.id IS NOT NULL THEN 'complete'
        WHEN datang.id IS NOT NULL AND pulang.id IS NULL THEN 'incomplete'
        ELSE 'unknown'
    END as status,
    CASE 
        WHEN datang.attendance_time IS NOT NULL AND pulang.attendance_time IS NOT NULL 
        THEN EXTRACT(EPOCH FROM (pulang.attendance_time - datang.attendance_time)) / 60
        ELSE NULL
    END as duration_minutes,
    datang.ip_address as checkin_ip,
    pulang.ip_address as checkout_ip,
    COALESCE(datang.created_at, pulang.created_at) as created_at
FROM students s
LEFT JOIN student_attendance datang ON s.id = datang.student_id 
    AND datang.attendance_type = 'datang'
LEFT JOIN student_attendance pulang ON s.id = pulang.student_id 
    AND pulang.attendance_type = 'pulang'
    AND pulang.attendance_date = datang.attendance_date
WHERE datang.id IS NOT NULL OR pulang.id IS NOT NULL
ORDER BY attendance_date DESC, checkin_time DESC;

-- Test the view
SELECT student_nim, student_name, attendance_date, checkin_time, checkout_time, 
       duration_minutes, status 
FROM attendance_daily_view 
LIMIT 5;

-- Success message
DO $$
BEGIN
    RAISE NOTICE '';
    RAISE NOTICE '==============================================';
    RAISE NOTICE 'attendance_daily_view updated successfully!';
    RAISE NOTICE 'Column names now match PHP expectations:';
    RAISE NOTICE '- student_nim, student_name';
    RAISE NOTICE '- checkin_time, checkout_time';
    RAISE NOTICE '- duration_minutes (numeric)';
    RAISE NOTICE '- status (complete/incomplete)';
    RAISE NOTICE '==============================================';
END $$;
