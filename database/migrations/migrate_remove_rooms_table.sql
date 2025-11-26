-- -- Migration: Remove rooms table and simplify borrowings structure
-- -- Date: 2025-11-25
-- -- Description: Menghapus tabel rooms dan relasi, menggantinya dengan kolom room_name di borrowings

-- BEGIN;

-- -- 1. Drop views yang bergantung pada room_id
-- DROP VIEW IF EXISTS borrowing_details_view CASCADE;
-- DROP VIEW IF EXISTS borrowing_history_view CASCADE;
-- DROP VIEW IF EXISTS room_availability_view CASCADE;

-- -- 2. Drop function yang terkait rooms
-- DROP FUNCTION IF EXISTS check_room_availability(INTEGER, TIMESTAMP, TIMESTAMP, INTEGER) CASCADE;

-- -- 3. Drop constraint check yang mengharuskan room_id
-- ALTER TABLE borrowings DROP CONSTRAINT IF EXISTS chk_borrowing_type_consistency CASCADE;

-- -- 4. Alter borrowings table
-- ALTER TABLE borrowings DROP CONSTRAINT IF EXISTS fk_borrowings_room CASCADE;
-- ALTER TABLE borrowings DROP COLUMN IF EXISTS room_id CASCADE;
-- ALTER TABLE borrowings ADD COLUMN IF NOT EXISTS room_name VARCHAR(100);

-- -- 5. Drop rooms table
-- DROP TABLE IF EXISTS rooms CASCADE;

-- -- 6. Recreate check constraint untuk type tanpa room_id requirement
-- ALTER TABLE borrowings ADD CONSTRAINT borrowings_type_check_new 
-- CHECK (type IN ('equipment', 'room'));

-- -- 7. Recreate borrowing_details_view with room_name
-- CREATE OR REPLACE VIEW borrowing_details_view AS
-- SELECT 
--     b.id AS borrowing_id,
--     b.borrower_name,
--     b.occupation,
--     b.type AS borrowing_type,
--     b.status,
--     b.borrow_date,
--     b.return_date,
--     b.room_name,
--     b.notes,
--     b.created_at,
--     b.updated_at,
--     -- For equipment borrowing
--     string_agg(DISTINCT e.name, ', ') AS equipment_names,
--     COUNT(DISTINCT be.equipment_id) AS total_items
-- FROM borrowings b
-- LEFT JOIN borrowing_equipment be ON b.id = be.borrowing_id
-- LEFT JOIN equipment e ON be.equipment_id = e.id
-- GROUP BY b.id, b.borrower_name, b.occupation, b.type, b.status, 
--          b.borrow_date, b.return_date, b.room_name, b.notes, b.created_at, b.updated_at
-- ORDER BY b.created_at DESC;

-- -- 8. Recreate borrowing_history_view
-- CREATE OR REPLACE VIEW borrowing_history_view AS
-- SELECT 
--     b.id AS borrowing_id,
--     b.borrower_name,
--     b.occupation,
--     b.type AS borrowing_type,
--     b.borrow_date,
--     b.return_date,
--     b.room_name,
--     b.notes,
--     -- For equipment borrowing
--     string_agg(DISTINCT e.name, ', ') AS equipment_names,
--     COUNT(DISTINCT be.equipment_id) AS total_items,
--     -- Duration calculation
--     EXTRACT(EPOCH FROM (b.return_date - b.borrow_date)) / 3600 AS duration_hours
-- FROM borrowings b
-- LEFT JOIN borrowing_equipment be ON b.id = be.borrowing_id
-- LEFT JOIN equipment e ON be.equipment_id = e.id
-- WHERE b.status = 'returned'
-- GROUP BY b.id, b.borrower_name, b.occupation, b.type, 
--          b.borrow_date, b.return_date, b.room_name, b.notes
-- ORDER BY b.return_date DESC;

-- COMMIT;

-- -- Success message
-- SELECT 'Migration completed successfully! Rooms table removed, borrowings now has room_name column.' AS result;
