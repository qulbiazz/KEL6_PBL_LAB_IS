-- ============================================================================
-- DATA MIGRATION: Migrate existing borrowing data to new structure
-- ============================================================================
-- Purpose: Migrate data from old structure (item_name, item_id) to new structure
-- (borrowing_equipment junction table and room_id foreign key)
-- ============================================================================

BEGIN;

-- ============================================================================
-- STEP 1: Backup existing data
-- ============================================================================

-- Create backup table
DROP TABLE IF EXISTS borrowings_backup;
CREATE TABLE borrowings_backup AS 
SELECT * FROM borrowings;

-- Comment cannot use concatenation in PostgreSQL
DO $$
BEGIN
    EXECUTE 'COMMENT ON TABLE borrowings_backup IS ''Backup of borrowings table before migration on ' || CURRENT_TIMESTAMP || '''';
END $$;

-- ============================================================================
-- STEP 2: Migrate ROOM borrowings
-- ============================================================================

-- Update room borrowings to use room_id foreign key
UPDATE borrowings b
SET room_id = r.id
FROM rooms r
WHERE b.type = 'room'
AND b.room_id IS NULL
AND (
    -- Match by item_id
    (b.item_id IS NOT NULL AND b.item_id::text = r.id::text)
    OR
    -- Match by item_name
    (b.item_name = r.name)
);

-- Log room borrowings that couldn't be matched
DO $$
DECLARE
    unmatched_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO unmatched_count
    FROM borrowings
    WHERE type = 'room'
    AND room_id IS NULL;
    
    IF unmatched_count > 0 THEN
        RAISE NOTICE 'Warning: % room borrowing(s) could not be matched to existing rooms', unmatched_count;
        
        -- Show details
        RAISE NOTICE 'Unmatched room borrowings:';
        FOR i IN (
            SELECT id, borrower_name, item_name, item_id
            FROM borrowings
            WHERE type = 'room' AND room_id IS NULL
            LIMIT 10
        ) LOOP
            RAISE NOTICE 'ID: %, Borrower: %, Item Name: %, Item ID: %', 
                i.id, i.borrower_name, i.item_name, i.item_id;
        END LOOP;
    ELSE
        RAISE NOTICE 'All room borrowings successfully migrated';
    END IF;
END $$;

-- ============================================================================
-- STEP 3: Migrate EQUIPMENT borrowings (single item)
-- ============================================================================

-- Migrate single equipment borrowings (where item_id is set and not in IDS: format)
INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
SELECT 
    b.id,
    b.item_id::INTEGER,
    1
FROM borrowings b
WHERE b.type = 'equipment'
AND b.item_id IS NOT NULL
AND b.item_id ~ '^\d+$' -- item_id is numeric
AND NOT EXISTS (
    SELECT 1 FROM borrowing_equipment be 
    WHERE be.borrowing_id = b.id
)
ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;

RAISE NOTICE 'Single equipment borrowings migrated';

-- ============================================================================
-- STEP 4: Migrate EQUIPMENT borrowings (multiple items with IDS: format)
-- ============================================================================

-- Migrate multiple equipment borrowings (IDS:1,2,3|Names format)
DO $$
DECLARE
    borrowing_rec RECORD;
    ids_part TEXT;
    names_part TEXT;
    equipment_ids TEXT[];
    equipment_id TEXT;
    matched_count INTEGER := 0;
    failed_count INTEGER := 0;
BEGIN
    -- Loop through all equipment borrowings with IDS: prefix
    FOR borrowing_rec IN (
        SELECT id, item_name
        FROM borrowings
        WHERE type = 'equipment'
        AND item_name LIKE 'IDS:%'
    ) LOOP
        BEGIN
            -- Split IDS:1,2,3|Equipment1, Equipment2 format
            ids_part := SPLIT_PART(borrowing_rec.item_name, '|', 1);
            ids_part := REPLACE(ids_part, 'IDS:', '');
            
            -- Split comma-separated IDs
            equipment_ids := STRING_TO_ARRAY(ids_part, ',');
            
            -- Insert each equipment item into junction table
            FOREACH equipment_id IN ARRAY equipment_ids
            LOOP
                equipment_id := TRIM(equipment_id);
                
                IF equipment_id ~ '^\d+$' THEN
                    -- Insert into junction table
                    INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
                    VALUES (borrowing_rec.id, equipment_id::INTEGER, 1)
                    ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;
                    
                    matched_count := matched_count + 1;
                END IF;
            END LOOP;
            
        EXCEPTION WHEN OTHERS THEN
            failed_count := failed_count + 1;
            RAISE NOTICE 'Failed to migrate borrowing ID %: %', borrowing_rec.id, SQLERRM;
        END;
    END LOOP;
    
    RAISE NOTICE 'Multiple equipment migration complete: % items migrated, % failed', matched_count, failed_count;
END $$;

-- ============================================================================
-- STEP 5: Migrate EQUIPMENT borrowings by name matching (fallback)
-- ============================================================================

-- For equipment borrowings without item_id, try to match by name
DO $$
DECLARE
    borrowing_rec RECORD;
    equipment_names TEXT[];
    equipment_name TEXT;
    equipment_id INTEGER;
    matched_count INTEGER := 0;
BEGIN
    FOR borrowing_rec IN (
        SELECT b.id, b.item_name
        FROM borrowings b
        WHERE b.type = 'equipment'
        AND b.item_id IS NULL
        AND b.item_name NOT LIKE 'IDS:%'
        AND NOT EXISTS (
            SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = b.id
        )
    ) LOOP
        BEGIN
            -- Split comma-separated names
            equipment_names := STRING_TO_ARRAY(borrowing_rec.item_name, ',');
            
            FOREACH equipment_name IN ARRAY equipment_names
            LOOP
                equipment_name := TRIM(equipment_name);
                
                -- Try to find matching equipment by name
                SELECT id INTO equipment_id
                FROM equipment
                WHERE LOWER(name) = LOWER(equipment_name)
                LIMIT 1;
                
                IF FOUND THEN
                    INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
                    VALUES (borrowing_rec.id, equipment_id, 1)
                    ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;
                    
                    matched_count := matched_count + 1;
                ELSE
                    RAISE NOTICE 'Could not find equipment: % for borrowing ID %', equipment_name, borrowing_rec.id;
                END IF;
            END LOOP;
            
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Failed to migrate borrowing ID %: %', borrowing_rec.id, SQLERRM;
        END;
    END LOOP;
    
    RAISE NOTICE 'Name-based equipment migration: % items matched', matched_count;
END $$;

-- ============================================================================
-- STEP 6: Recalculate Equipment Available Quantities
-- ============================================================================

-- Reset all equipment available quantities to total quantity
UPDATE equipment
SET available_quantity = quantity;

-- Deduct quantities for active borrowings
UPDATE equipment e
SET available_quantity = e.quantity - COALESCE(
    (SELECT SUM(be.quantity)
     FROM borrowing_equipment be
     JOIN borrowings b ON be.borrowing_id = b.id
     WHERE be.equipment_id = e.id
     AND b.status = 'active'),
    0
);

-- Ensure available_quantity doesn't go negative
UPDATE equipment
SET available_quantity = GREATEST(available_quantity, 0);

RAISE NOTICE 'Equipment available quantities recalculated';

-- ============================================================================
-- STEP 7: Verification and Statistics
-- ============================================================================

DO $$
DECLARE
    total_borrowings INTEGER;
    room_borrowings INTEGER;
    equipment_borrowings INTEGER;
    migrated_equipment_items INTEGER;
    unmigrated_equipment INTEGER;
    room_with_fk INTEGER;
BEGIN
    -- Count statistics
    SELECT COUNT(*) INTO total_borrowings FROM borrowings;
    SELECT COUNT(*) INTO room_borrowings FROM borrowings WHERE type = 'room';
    SELECT COUNT(*) INTO equipment_borrowings FROM borrowings WHERE type = 'equipment';
    SELECT COUNT(*) INTO migrated_equipment_items FROM borrowing_equipment;
    SELECT COUNT(*) INTO room_with_fk FROM borrowings WHERE type = 'room' AND room_id IS NOT NULL;
    
    SELECT COUNT(*) INTO unmigrated_equipment
    FROM borrowings b
    WHERE b.type = 'equipment'
    AND NOT EXISTS (
        SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = b.id
    );
    
    -- Display statistics
    RAISE NOTICE '========================================';
    RAISE NOTICE 'MIGRATION STATISTICS';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Total borrowings: %', total_borrowings;
    RAISE NOTICE 'Room borrowings: %', room_borrowings;
    RAISE NOTICE '  - With room_id FK: %', room_with_fk;
    RAISE NOTICE 'Equipment borrowings: %', equipment_borrowings;
    RAISE NOTICE '  - Equipment items in junction table: %', migrated_equipment_items;
    RAISE NOTICE '  - Unmigrated equipment borrowings: %', unmigrated_equipment;
    RAISE NOTICE '========================================';
    
    -- Show unmigrated items if any
    IF unmigrated_equipment > 0 THEN
        RAISE NOTICE 'Unmigrated equipment borrowings (first 10):';
        FOR i IN (
            SELECT b.id, b.borrower_name, b.item_name, b.item_id, b.status
            FROM borrowings b
            WHERE b.type = 'equipment'
            AND NOT EXISTS (
                SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = b.id
            )
            LIMIT 10
        ) LOOP
            RAISE NOTICE 'ID: %, Borrower: %, Item: %, Status: %', 
                i.id, i.borrower_name, i.item_name, i.status;
        END LOOP;
    END IF;
END $$;

-- ============================================================================
-- STEP 8: Optional - Clean up deprecated columns (COMMENTED OUT FOR SAFETY)
-- ============================================================================

-- IMPORTANT: Only run this after verifying migration is successful
-- and all application code has been updated to use new structure

/*
-- Mark deprecated columns as such (don't drop yet)
COMMENT ON COLUMN borrowings.item_name IS 'DEPRECATED - Data migrated to borrowing_equipment and room_id';
COMMENT ON COLUMN borrowings.item_id IS 'DEPRECATED - Data migrated to borrowing_equipment and room_id';  
COMMENT ON COLUMN borrowings.item_type IS 'DEPRECATED - Use type column instead';

-- To drop deprecated columns later (after thorough testing):
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_name;
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_id;
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS item_type;
*/

COMMIT;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Run these queries to verify migration success:

-- Check borrowing details view
-- SELECT * FROM borrowing_details_view ORDER BY borrowing_id DESC LIMIT 10;

-- Check equipment availability
-- SELECT * FROM equipment_availability_view ORDER BY equipment_name;

-- Check for any unmigrated data
-- SELECT * FROM borrowings WHERE type = 'equipment' 
--   AND NOT EXISTS (SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = borrowings.id);

-- SELECT * FROM borrowings WHERE type = 'room' AND room_id IS NULL;

-- Compare with backup
-- SELECT COUNT(*) FROM borrowings;
-- SELECT COUNT(*) FROM borrowings_backup;

RAISE NOTICE 'Migration completed successfully!';
RAISE NOTICE 'Please run verification queries to ensure data integrity';
RAISE NOTICE 'Backup table "borrowings_backup" has been created for safety';
