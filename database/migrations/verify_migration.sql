-- ============================================================================
-- VERIFICATION & TESTING SCRIPT
-- ============================================================================
-- Run this after migration to verify everything is working correctly
-- ============================================================================

\set ON_ERROR_STOP on
\timing on

\echo ''
\echo '========================================='
\echo 'DATABASE VERIFICATION & TESTING'
\echo '========================================='
\echo ''

-- ============================================================================
-- 1. CHECK TABLE STRUCTURE
-- ============================================================================

\echo '1. Checking table structure...'
\echo '   - borrowings table'
SELECT 
    column_name, 
    data_type, 
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_name = 'borrowings'
ORDER BY ordinal_position;

\echo '   - borrowing_equipment junction table'
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns
WHERE table_name = 'borrowing_equipment'
ORDER BY ordinal_position;

-- ============================================================================
-- 2. CHECK FOREIGN KEYS
-- ============================================================================

\echo ''
\echo '2. Checking foreign key constraints...'
SELECT
    tc.table_name,
    tc.constraint_name,
    tc.constraint_type,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.constraint_type = 'FOREIGN KEY'
AND tc.table_name IN ('borrowings', 'borrowing_equipment')
ORDER BY tc.table_name, tc.constraint_name;

-- ============================================================================
-- 3. CHECK VIEWS
-- ============================================================================

\echo ''
\echo '3. Checking views exist...'
SELECT 
    table_name as view_name,
    view_definition
FROM information_schema.views
WHERE table_schema = 'public'
AND table_name LIKE '%borrowing%' OR table_name LIKE '%availability%'
ORDER BY table_name;

\echo ''
\echo '   Sample data from views:'
\echo '   - borrowing_details_view (first 3 rows)'
SELECT 
    borrowing_id,
    borrower_name,
    borrowing_type,
    status,
    CASE 
        WHEN borrowing_type = 'room' THEN room_name
        ELSE equipment_names
    END as items
FROM borrowing_details_view
ORDER BY borrowing_id DESC
LIMIT 3;

\echo ''
\echo '   - equipment_availability_view'
SELECT 
    equipment_name,
    total_quantity,
    current_available,
    currently_borrowed,
    availability_status
FROM equipment_availability_view
ORDER BY equipment_name
LIMIT 5;

\echo ''
\echo '   - room_availability_view'
SELECT 
    room_name,
    is_currently_booked,
    current_borrower,
    next_booking_date
FROM room_availability_view
ORDER BY room_name;

-- ============================================================================
-- 4. CHECK FUNCTIONS
-- ============================================================================

\echo ''
\echo '4. Checking database functions...'
SELECT 
    routine_name,
    routine_type,
    data_type as return_type
FROM information_schema.routines
WHERE routine_schema = 'public'
AND (routine_name LIKE '%availability%' OR routine_name LIKE '%borrowing%')
ORDER BY routine_name;

\echo ''
\echo '   Testing functions:'
\echo '   - check_equipment_availability (should return true for available items)'
SELECT 
    e.id,
    e.name,
    e.available_quantity,
    check_equipment_availability(e.id, 1) as is_available
FROM equipment e
WHERE e.is_active = true
LIMIT 3;

-- ============================================================================
-- 5. CHECK TRIGGERS
-- ============================================================================

\echo ''
\echo '5. Checking triggers...'
SELECT 
    trigger_name,
    event_object_table as table_name,
    action_timing,
    event_manipulation as event
FROM information_schema.triggers
WHERE trigger_schema = 'public'
ORDER BY event_object_table, trigger_name;

-- ============================================================================
-- 6. CHECK INDEXES
-- ============================================================================

\echo ''
\echo '6. Checking indexes...'
SELECT
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
AND (tablename = 'borrowings' OR tablename = 'borrowing_equipment')
ORDER BY tablename, indexname;

-- ============================================================================
-- 7. DATA INTEGRITY CHECKS
-- ============================================================================

\echo ''
\echo '7. Data integrity checks...'

\echo '   - Checking for orphaned borrowing_equipment records'
SELECT COUNT(*) as orphaned_count
FROM borrowing_equipment be
WHERE NOT EXISTS (
    SELECT 1 FROM borrowings b WHERE b.id = be.borrowing_id
);

\echo '   - Checking for equipment borrowings without junction records'
SELECT COUNT(*) as missing_junction_count
FROM borrowings b
WHERE b.type = 'equipment'
AND NOT EXISTS (
    SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = b.id
);

\echo '   - Checking for room borrowings without room_id'
SELECT COUNT(*) as missing_room_id_count
FROM borrowings
WHERE type = 'room'
AND room_id IS NULL;

\echo '   - Checking equipment quantity consistency'
SELECT 
    e.id,
    e.name,
    e.quantity as total,
    e.available_quantity as available_in_db,
    e.quantity - COALESCE(
        (SELECT SUM(be.quantity)
         FROM borrowing_equipment be
         JOIN borrowings b ON be.borrowing_id = b.id
         WHERE be.equipment_id = e.id
         AND b.status = 'active'),
        0
    ) as expected_available,
    CASE 
        WHEN e.available_quantity = e.quantity - COALESCE(
            (SELECT SUM(be.quantity)
             FROM borrowing_equipment be
             JOIN borrowings b ON be.borrowing_id = b.id
             WHERE be.equipment_id = e.id
             AND b.status = 'active'),
            0
        ) THEN '✓ OK'
        ELSE '✗ MISMATCH'
    END as status
FROM equipment e
WHERE e.is_active = true;

-- ============================================================================
-- 8. STATISTICS
-- ============================================================================

\echo ''
\echo '8. Database statistics...'

\echo '   - Borrowings summary'
SELECT 
    type,
    status,
    COUNT(*) as count
FROM borrowings
GROUP BY ROLLUP(type, status)
ORDER BY type NULLS LAST, status NULLS LAST;

\echo '   - Equipment borrowing details'
SELECT 
    e.name as equipment_name,
    COUNT(DISTINCT be.borrowing_id) as times_borrowed,
    SUM(be.quantity) as total_quantity_borrowed,
    COUNT(CASE WHEN b.status = 'active' THEN 1 END) as currently_borrowed
FROM equipment e
LEFT JOIN borrowing_equipment be ON e.id = be.equipment_id
LEFT JOIN borrowings b ON be.borrowing_id = b.id
WHERE e.is_active = true
GROUP BY e.id, e.name
ORDER BY times_borrowed DESC;

\echo '   - Room booking statistics'
SELECT 
    r.name as room_name,
    COUNT(b.id) as total_bookings,
    COUNT(CASE WHEN b.status = 'active' THEN 1 END) as active_bookings,
    COUNT(CASE WHEN b.status = 'returned' THEN 1 END) as completed_bookings
FROM rooms r
LEFT JOIN borrowings b ON r.id = b.room_id
WHERE r.is_active = true
GROUP BY r.id, r.name
ORDER BY total_bookings DESC;

-- ============================================================================
-- 9. PERFORMANCE TEST
-- ============================================================================

\echo ''
\echo '9. Performance testing (EXPLAIN ANALYZE)...'

\echo '   - borrowing_details_view query performance'
EXPLAIN ANALYZE
SELECT * FROM borrowing_details_view
WHERE status = 'active'
ORDER BY borrowing_id DESC
LIMIT 10;

\echo ''
\echo '   - equipment_availability_view query performance'
EXPLAIN ANALYZE
SELECT * FROM equipment_availability_view
WHERE availability_status = 'available';

-- ============================================================================
-- 10. SAMPLE QUERIES
-- ============================================================================

\echo ''
\echo '10. Sample queries to test functionality...'

\echo '   - Get all active borrowings with details'
SELECT 
    borrowing_id,
    borrower_name,
    borrowing_type,
    CASE 
        WHEN borrowing_type = 'room' THEN room_name
        ELSE equipment_names
    END as items,
    borrow_date,
    return_date,
    calculated_status
FROM borrowing_details_view
WHERE status = 'active'
ORDER BY borrow_date DESC
LIMIT 5;

\echo ''
\echo '   - Get equipment with low stock (< 20%)'
SELECT 
    equipment_name,
    total_quantity,
    current_available,
    currently_borrowed,
    availability_status
FROM equipment_availability_view
WHERE availability_status = 'low_stock';

\echo ''
\echo '   - Get currently booked rooms'
SELECT 
    room_name,
    current_borrower,
    current_booking_until
FROM room_availability_view
WHERE is_currently_booked = true;

-- ============================================================================
-- 11. BACKUP VERIFICATION
-- ============================================================================

\echo ''
\echo '11. Verifying backup table...'

\echo '   - Backup table exists'
SELECT 
    CASE 
        WHEN EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'borrowings_backup')
        THEN '✓ Backup table exists'
        ELSE '✗ No backup table found'
    END as backup_status;

\echo '   - Comparing record counts'
SELECT 
    'Original' as source,
    COUNT(*) as record_count
FROM borrowings_backup
UNION ALL
SELECT 
    'Current' as source,
    COUNT(*) as record_count
FROM borrowings;

-- ============================================================================
-- FINAL REPORT
-- ============================================================================

\echo ''
\echo '========================================='
\echo 'VERIFICATION COMPLETE'
\echo '========================================='
\echo ''
\echo 'If all checks passed:'
\echo '  ✓ Database structure is correct'
\echo '  ✓ Views are working'
\echo '  ✓ Functions and triggers are active'
\echo '  ✓ Data integrity is maintained'
\echo '  ✓ Indexes are in place'
\echo ''
\echo 'Next steps:'
\echo '  1. Update application code to use new model'
\echo '  2. Test create/update/delete operations'
\echo '  3. Monitor performance in production'
\echo '  4. Consider dropping deprecated columns after testing'
\echo ''

-- ============================================================================
-- CLEANUP COMMANDS (COMMENTED OUT)
-- ============================================================================

-- Uncomment these after thorough testing in production:

-- Drop deprecated columns
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_name;
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_id;
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_type;

-- Drop backup table (after confirming everything works)
-- DROP TABLE IF EXISTS borrowings_backup;
