-- ============================================================================
-- ROLLBACK SCRIPT - Use only if migration fails or needs to be reverted
-- ============================================================================
-- ⚠️ WARNING: This will revert database to pre-migration state
-- Only use this if you need to rollback the migration
-- ============================================================================

\set ON_ERROR_STOP on

\echo ''
\echo '========================================='
\echo '⚠️  ROLLBACK MIGRATION'
\echo '========================================='
\echo ''
\echo 'This will:'
\echo '  1. Drop new views'
\echo '  2. Drop new functions and triggers'
\echo '  3. Drop borrowing_equipment junction table'
\echo '  4. Restore borrowings from backup (if exists)'
\echo '  5. Remove new columns from borrowings'
\echo ''
\echo 'Press Ctrl+C now to cancel, or wait 5 seconds to continue...'
\echo ''

-- Give user time to cancel
SELECT pg_sleep(5);

BEGIN;

\echo 'Starting rollback...'

-- ============================================================================
-- STEP 1: Drop Views
-- ============================================================================

\echo 'Step 1: Dropping views...'

DROP VIEW IF EXISTS public.borrowing_history_view CASCADE;
DROP VIEW IF EXISTS public.borrowing_details_view CASCADE;
DROP VIEW IF EXISTS public.equipment_availability_view CASCADE;
DROP VIEW IF EXISTS public.room_availability_view CASCADE;

\echo '  ✓ Views dropped'

-- ============================================================================
-- STEP 2: Drop Triggers
-- ============================================================================

\echo 'Step 2: Dropping triggers...'

DROP TRIGGER IF EXISTS trigger_update_borrowing_timestamp ON public.borrowings;
DROP TRIGGER IF EXISTS trigger_equipment_quantity_on_borrowing ON public.borrowing_equipment;
DROP TRIGGER IF EXISTS trigger_equipment_quantity_on_return ON public.borrowings;

\echo '  ✓ Triggers dropped'

-- ============================================================================
-- STEP 3: Drop Functions
-- ============================================================================

\echo 'Step 3: Dropping functions...'

DROP FUNCTION IF EXISTS update_borrowing_timestamp() CASCADE;
DROP FUNCTION IF EXISTS update_equipment_quantity_on_borrowing() CASCADE;
DROP FUNCTION IF EXISTS update_equipment_quantity_on_return() CASCADE;
DROP FUNCTION IF EXISTS check_equipment_availability(INTEGER, INTEGER) CASCADE;
DROP FUNCTION IF EXISTS check_room_availability(INTEGER, TIMESTAMP, TIMESTAMP, INTEGER) CASCADE;

\echo '  ✓ Functions dropped'

-- ============================================================================
-- STEP 4: Drop Junction Table
-- ============================================================================

\echo 'Step 4: Dropping borrowing_equipment junction table...'

DROP TABLE IF EXISTS public.borrowing_equipment CASCADE;

\echo '  ✓ Junction table dropped'

-- ============================================================================
-- STEP 5: Remove New Columns from Borrowings
-- ============================================================================

\echo 'Step 5: Removing new columns from borrowings table...'

ALTER TABLE public.borrowings DROP CONSTRAINT IF EXISTS chk_borrowing_type_consistency;
ALTER TABLE public.borrowings DROP CONSTRAINT IF EXISTS fk_borrowings_room;

ALTER TABLE public.borrowings DROP COLUMN IF EXISTS room_id;
ALTER TABLE public.borrowings DROP COLUMN IF EXISTS notes;
ALTER TABLE public.borrowings DROP COLUMN IF EXISTS updated_at;

\echo '  ✓ New columns removed'

-- ============================================================================
-- STEP 6: Restore Data from Backup (if exists)
-- ============================================================================

\echo 'Step 6: Restoring data from backup...'

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'borrowings_backup') THEN
        -- Truncate current data
        TRUNCATE public.borrowings RESTART IDENTITY CASCADE;
        
        -- Restore from backup
        INSERT INTO public.borrowings 
        SELECT * FROM borrowings_backup;
        
        -- Update sequence
        SELECT setval('borrowings_id_seq', COALESCE(MAX(id), 1)) 
        FROM public.borrowings;
        
        RAISE NOTICE '  ✓ Data restored from backup';
    ELSE
        RAISE NOTICE '  ⚠ No backup table found - data not restored';
    END IF;
END $$;

-- ============================================================================
-- STEP 7: Recalculate Equipment Quantities
-- ============================================================================

\echo 'Step 7: Recalculating equipment quantities...'

-- Reset to total quantity first
UPDATE equipment SET available_quantity = quantity;

-- This is best-effort - old structure doesn't have accurate quantity tracking
-- Manual review may be needed

\echo '  ⚠ Equipment quantities reset to total quantity'
\echo '     Manual review recommended'

-- ============================================================================
-- STEP 8: Drop Backup Table (Optional)
-- ============================================================================

-- Uncomment to drop backup table after rollback
-- DROP TABLE IF EXISTS borrowings_backup;

-- ============================================================================
-- STEP 9: Verification
-- ============================================================================

\echo ''
\echo 'Step 9: Verifying rollback...'

\echo '  - Checking borrowings table structure'
SELECT 
    column_name,
    data_type
FROM information_schema.columns
WHERE table_name = 'borrowings'
ORDER BY ordinal_position;

\echo '  - Record count'
SELECT COUNT(*) as total_records FROM borrowings;

\echo '  - Checking views (should be 0)'
SELECT COUNT(*) as view_count
FROM information_schema.views
WHERE table_schema = 'public'
AND (table_name LIKE '%borrowing%' OR table_name LIKE '%availability%');

\echo '  - Checking functions (should be 0)'
SELECT COUNT(*) as function_count
FROM information_schema.routines
WHERE routine_schema = 'public'
AND (routine_name LIKE '%availability%' OR routine_name LIKE '%borrowing%');

\echo '  - Checking triggers (should be minimal)'
SELECT 
    trigger_name,
    event_object_table
FROM information_schema.triggers
WHERE trigger_schema = 'public'
AND event_object_table IN ('borrowings', 'borrowing_equipment');

COMMIT;

-- ============================================================================
-- FINAL REPORT
-- ============================================================================

\echo ''
\echo '========================================='
\echo 'ROLLBACK COMPLETE'
\echo '========================================='
\echo ''
\echo 'What was reverted:'
\echo '  ✓ Views removed'
\echo '  ✓ Functions removed'
\echo '  ✓ Triggers removed'
\echo '  ✓ Junction table removed'
\echo '  ✓ New columns removed'
\echo '  ✓ Data restored from backup (if available)'
\echo ''
\echo 'Next steps:'
\echo '  1. Verify old application code still works'
\echo '  2. Review equipment quantities (may need manual adjustment)'
\echo '  3. Investigate why rollback was needed'
\echo '  4. Fix issues before attempting migration again'
\echo ''
\echo 'To drop backup table (if no longer needed):'
\echo '  DROP TABLE IF EXISTS borrowings_backup;'
\echo ''

-- ============================================================================
-- MANUAL CLEANUP (if needed)
-- ============================================================================

/*
-- If you need to completely reset to original state:

-- 1. Restore original borrowings table structure
ALTER TABLE public.borrowings 
    ALTER COLUMN borrow_date TYPE DATE USING borrow_date::date,
    ALTER COLUMN return_date TYPE DATE USING return_date::date;

-- 2. Drop any remaining constraints
ALTER TABLE borrowings DROP CONSTRAINT IF EXISTS borrowings_type_check;
ALTER TABLE borrowings 
    ADD CONSTRAINT borrowings_type_check 
    CHECK (type IN ('equipment', 'room'));

-- 3. Drop backup table
DROP TABLE IF EXISTS borrowings_backup;

-- 4. Vacuum to reclaim space
VACUUM FULL borrowings;
ANALYZE borrowings;
*/
