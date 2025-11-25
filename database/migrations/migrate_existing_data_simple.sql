-- ============================================================================
-- DATA MIGRATION: Migrate existing borrowing data to new structure (SIMPLIFIED)
-- ============================================================================

BEGIN;

-- ============================================================================
-- STEP 1: Backup existing data
-- ============================================================================

DROP TABLE IF EXISTS borrowings_backup;
CREATE TABLE borrowings_backup AS SELECT * FROM borrowings;

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
    (b.item_id IS NOT NULL AND b.item_id::text = r.id::text)
    OR
    (b.item_name = r.name)
);

-- ============================================================================
-- STEP 3: Migrate SINGLE EQUIPMENT borrowings
-- ============================================================================

INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
SELECT 
    b.id,
    b.item_id::INTEGER,
    1
FROM borrowings b
WHERE b.type = 'equipment'
AND b.item_id IS NOT NULL
AND b.item_id::TEXT ~ '^\d+$'
AND NOT EXISTS (
    SELECT 1 FROM borrowing_equipment be WHERE be.borrowing_id = b.id
)
ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;

-- ============================================================================
-- STEP 4: Migrate MULTIPLE EQUIPMENT borrowings (IDS: format)
-- ============================================================================

DO $$
DECLARE
    borrowing_rec RECORD;
    ids_part TEXT;
    equipment_ids TEXT[];
    equipment_id TEXT;
BEGIN
    FOR borrowing_rec IN (
        SELECT id, item_name
        FROM borrowings
        WHERE type = 'equipment'
        AND item_name LIKE 'IDS:%'
    ) LOOP
        -- Split IDS:1,2,3|Equipment1, Equipment2 format
        ids_part := SPLIT_PART(borrowing_rec.item_name, '|', 1);
        ids_part := REPLACE(ids_part, 'IDS:', '');
        equipment_ids := STRING_TO_ARRAY(ids_part, ',');
        
        -- Insert each equipment item
        FOREACH equipment_id IN ARRAY equipment_ids
        LOOP
            equipment_id := TRIM(equipment_id);
            
            IF equipment_id ~ '^\d+$' THEN
                INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
                VALUES (borrowing_rec.id, equipment_id::INTEGER, 1)
                ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;
            END IF;
        END LOOP;
    END LOOP;
END $$;

-- ============================================================================
-- STEP 5: Migrate EQUIPMENT by name matching (fallback)
-- ============================================================================

DO $$
DECLARE
    borrowing_rec RECORD;
    equipment_names TEXT[];
    equipment_name TEXT;
    equipment_id INTEGER;
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
        equipment_names := STRING_TO_ARRAY(borrowing_rec.item_name, ',');
        
        FOREACH equipment_name IN ARRAY equipment_names
        LOOP
            equipment_name := TRIM(equipment_name);
            
            SELECT id INTO equipment_id
            FROM equipment
            WHERE LOWER(name) = LOWER(equipment_name)
            LIMIT 1;
            
            IF FOUND THEN
                INSERT INTO borrowing_equipment (borrowing_id, equipment_id, quantity)
                VALUES (borrowing_rec.id, equipment_id, 1)
                ON CONFLICT (borrowing_id, equipment_id) DO NOTHING;
            END IF;
        END LOOP;
    END LOOP;
END $$;

-- ============================================================================
-- STEP 6: Recalculate Equipment Quantities
-- ============================================================================

-- Reset all to total quantity first
UPDATE equipment SET available_quantity = quantity;

-- Deduct active borrowings
UPDATE equipment e
SET available_quantity = e.quantity - COALESCE(
    (SELECT SUM(be.quantity)
     FROM borrowing_equipment be
     JOIN borrowings b ON be.borrowing_id = b.id
     WHERE be.equipment_id = e.id
     AND b.status = 'active'),
    0
);

-- Ensure no negatives
UPDATE equipment SET available_quantity = GREATEST(available_quantity, 0);

-- ============================================================================
-- STEP 7: Show Statistics
-- ============================================================================

DO $$
DECLARE
    total_borrowings INTEGER;
    room_borrowings INTEGER;
    equipment_borrowings INTEGER;
    migrated_equipment_items INTEGER;
    room_with_fk INTEGER;
BEGIN
    SELECT COUNT(*) INTO total_borrowings FROM borrowings;
    SELECT COUNT(*) INTO room_borrowings FROM borrowings WHERE type = 'room';
    SELECT COUNT(*) INTO equipment_borrowings FROM borrowings WHERE type = 'equipment';
    SELECT COUNT(*) INTO migrated_equipment_items FROM borrowing_equipment;
    SELECT COUNT(*) INTO room_with_fk FROM borrowings WHERE type = 'room' AND room_id IS NOT NULL;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'MIGRATION STATISTICS';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Total borrowings: %', total_borrowings;
    RAISE NOTICE 'Room borrowings: % (% with room_id)', room_borrowings, room_with_fk;
    RAISE NOTICE 'Equipment borrowings: %', equipment_borrowings;
    RAISE NOTICE 'Equipment items in junction table: %', migrated_equipment_items;
    RAISE NOTICE '========================================';
END $$;

COMMIT;

-- Done!
