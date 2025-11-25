-- ============================================================================
-- MIGRATION: Optimized Database Structure for Borrowing System
-- ============================================================================
-- Purpose: 
-- 1. Implement proper relational structure with junction table
-- 2. Create database views for optimized queries
-- 3. Remove redundant data (item_name string storage)
-- 4. Improve data integrity and normalization
-- ============================================================================

BEGIN;

-- ============================================================================
-- STEP 1: Create Junction Table for Many-to-Many Equipment Borrowing
-- ============================================================================

CREATE TABLE IF NOT EXISTS public.borrowing_equipment (
    id SERIAL PRIMARY KEY,
    borrowing_id INTEGER NOT NULL,
    equipment_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    CONSTRAINT fk_borrowing_equipment_borrowing 
        FOREIGN KEY (borrowing_id) 
        REFERENCES public.borrowings(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_borrowing_equipment_equipment 
        FOREIGN KEY (equipment_id) 
        REFERENCES public.equipment(id) 
        ON DELETE RESTRICT,
    
    -- Prevent duplicate equipment in same borrowing
    CONSTRAINT uk_borrowing_equipment UNIQUE (borrowing_id, equipment_id),
    
    -- Quantity must be positive
    CONSTRAINT chk_quantity_positive CHECK (quantity > 0)
);

-- Create indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_borrowing_equipment_borrowing_id 
    ON public.borrowing_equipment(borrowing_id);
    
CREATE INDEX IF NOT EXISTS idx_borrowing_equipment_equipment_id 
    ON public.borrowing_equipment(equipment_id);

COMMENT ON TABLE public.borrowing_equipment IS 'Junction table for many-to-many relationship between borrowings and equipment';
COMMENT ON COLUMN public.borrowing_equipment.quantity IS 'Number of units borrowed for this equipment item';

-- ============================================================================
-- STEP 2: Alter Borrowings Table Structure
-- ============================================================================

-- Add new columns if they don't exist
DO $$ 
BEGIN
    -- Add room_id for room borrowings (one-to-many relationship)
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name='borrowings' AND column_name='room_id') THEN
        ALTER TABLE public.borrowings ADD COLUMN room_id INTEGER;
        
        ALTER TABLE public.borrowings
            ADD CONSTRAINT fk_borrowings_room 
            FOREIGN KEY (room_id) 
            REFERENCES public.rooms(id) 
            ON DELETE RESTRICT;
            
        CREATE INDEX idx_borrowings_room_id ON public.borrowings(room_id);
    END IF;
    
    -- Add notes column for additional information
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name='borrowings' AND column_name='notes') THEN
        ALTER TABLE public.borrowings ADD COLUMN notes TEXT;
    END IF;
    
    -- Add updated_at column for tracking changes
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name='borrowings' AND column_name='updated_at') THEN
        ALTER TABLE public.borrowings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
    END IF;
END $$;

-- Migrate room_id data before adding constraint
-- Match room borrowings with rooms table by item_id or item_name
UPDATE public.borrowings b
SET room_id = r.id
FROM public.rooms r
WHERE b.type = 'room'
AND b.room_id IS NULL
AND (
    -- Match by item_id (if it's a valid integer)
    (b.item_id IS NOT NULL AND b.item_id::text ~ '^\d+$' AND b.item_id::INTEGER = r.id)
    OR
    -- Match by item_name
    (b.item_name = r.name)
);

-- Add constraint to ensure either room_id OR equipment (via junction table) is set
-- Room borrowing: room_id NOT NULL, type = 'room'
-- Equipment borrowing: room_id NULL, type = 'equipment'
-- Note: This constraint will only be added if all room borrowings have room_id set
DO $$
DECLARE
    unmigrated_rooms INTEGER;
BEGIN
    -- Check if there are any room borrowings without room_id
    SELECT COUNT(*) INTO unmigrated_rooms
    FROM public.borrowings
    WHERE type = 'room' AND room_id IS NULL;
    
    IF unmigrated_rooms > 0 THEN
        RAISE NOTICE 'Warning: % room borrowing(s) without room_id found. Constraint not added.', unmigrated_rooms;
        RAISE NOTICE 'Please manually set room_id for these borrowings or they will be excluded from queries.';
    ELSE
        -- Safe to add constraint
        ALTER TABLE public.borrowings DROP CONSTRAINT IF EXISTS chk_borrowing_type_consistency;
        ALTER TABLE public.borrowings 
            ADD CONSTRAINT chk_borrowing_type_consistency 
            CHECK (
                (type = 'room' AND room_id IS NOT NULL) OR
                (type = 'equipment' AND room_id IS NULL)
            );
        RAISE NOTICE 'Constraint chk_borrowing_type_consistency added successfully';
    END IF;
END $$;

COMMENT ON COLUMN public.borrowings.room_id IS 'Foreign key to rooms table (only for room type borrowings)';
COMMENT ON COLUMN public.borrowings.item_name IS 'DEPRECATED: Use borrowing_equipment junction table or room_id instead';
COMMENT ON COLUMN public.borrowings.item_id IS 'DEPRECATED: Use borrowing_equipment junction table or room_id instead';
COMMENT ON COLUMN public.borrowings.item_type IS 'DEPRECATED: Use type column instead';

-- ============================================================================
-- STEP 3: Create Optimized Database Views
-- ============================================================================

-- View 1: Borrowing Details View
-- Purpose: Simplified query for displaying borrowing information
DROP VIEW IF EXISTS public.borrowing_details_view CASCADE;
CREATE VIEW public.borrowing_details_view AS
SELECT 
    b.id AS borrowing_id,
    b.borrower_name,
    b.occupation,
    b.type AS borrowing_type,
    b.borrow_date,
    b.return_date,
    b.status,
    b.notes,
    b.created_at,
    b.updated_at,
    
    -- Room information (for room borrowings)
    r.id AS room_id,
    r.name AS room_name,
    r.description AS room_description,
    r.capacity AS room_capacity,
    
    -- Equipment aggregation (for equipment borrowings)
    CASE 
        WHEN b.type = 'equipment' THEN
            (SELECT COUNT(*) FROM borrowing_equipment be WHERE be.borrowing_id = b.id)
        ELSE 0
    END AS equipment_count,
    
    CASE 
        WHEN b.type = 'equipment' THEN
            (SELECT STRING_AGG(e.name, ', ' ORDER BY e.name) 
             FROM borrowing_equipment be 
             JOIN equipment e ON be.equipment_id = e.id 
             WHERE be.borrowing_id = b.id)
        ELSE NULL
    END AS equipment_names,
    
    -- Status indicators
    CASE 
        WHEN b.status = 'active' AND b.return_date < CURRENT_TIMESTAMP THEN 'overdue'
        ELSE b.status
    END AS calculated_status,
    
    -- Duration calculation
    EXTRACT(EPOCH FROM (b.return_date - b.borrow_date))/86400 AS duration_days

FROM public.borrowings b
LEFT JOIN public.rooms r ON b.room_id = r.id;

COMMENT ON VIEW public.borrowing_details_view IS 'Complete borrowing information with room and equipment details aggregated';

-- View 2: Equipment Availability View
-- Purpose: Real-time equipment availability calculation
DROP VIEW IF EXISTS public.equipment_availability_view CASCADE;
CREATE VIEW public.equipment_availability_view AS
SELECT 
    e.id AS equipment_id,
    e.name AS equipment_name,
    e.description,
    e.quantity AS total_quantity,
    e.available_quantity AS current_available,
    e.display_order,
    e.is_active,
    
    -- Calculate currently borrowed (active borrowings)
    COALESCE(
        (SELECT SUM(be.quantity) 
         FROM borrowing_equipment be
         JOIN borrowings b ON be.borrowing_id = b.id
         WHERE be.equipment_id = e.id 
         AND b.status = 'active'),
        0
    ) AS currently_borrowed,
    
    -- Calculate expected availability
    e.quantity - COALESCE(
        (SELECT SUM(be.quantity) 
         FROM borrowing_equipment be
         JOIN borrowings b ON be.borrowing_id = b.id
         WHERE be.equipment_id = e.id 
         AND b.status = 'active'),
        0
    ) AS expected_available,
    
    -- Availability status
    CASE 
        WHEN e.is_active = false THEN 'inactive'
        WHEN e.available_quantity <= 0 THEN 'unavailable'
        WHEN e.available_quantity <= (e.quantity * 0.2) THEN 'low_stock'
        ELSE 'available'
    END AS availability_status,
    
    -- Borrowing statistics
    (SELECT COUNT(DISTINCT b.id)
     FROM borrowing_equipment be
     JOIN borrowings b ON be.borrowing_id = b.id
     WHERE be.equipment_id = e.id) AS total_borrowing_count,
     
    (SELECT MAX(b.return_date)
     FROM borrowing_equipment be
     JOIN borrowings b ON be.borrowing_id = b.id
     WHERE be.equipment_id = e.id 
     AND b.status = 'active') AS next_return_date

FROM public.equipment e;

COMMENT ON VIEW public.equipment_availability_view IS 'Real-time equipment availability with borrowing statistics';

-- View 3: Room Availability View
-- Purpose: Check room availability and booking status
DROP VIEW IF EXISTS public.room_availability_view CASCADE;
CREATE VIEW public.room_availability_view AS
SELECT 
    r.id AS room_id,
    r.name AS room_name,
    r.description,
    r.capacity,
    r.display_order,
    r.is_active,
    
    -- Current booking status
    EXISTS(
        SELECT 1 
        FROM borrowings b
        WHERE b.room_id = r.id
        AND b.status = 'active'
        AND b.borrow_date <= CURRENT_TIMESTAMP
        AND b.return_date >= CURRENT_TIMESTAMP
    ) AS is_currently_booked,
    
    -- Current booking details (if booked)
    (SELECT b.id 
     FROM borrowings b
     WHERE b.room_id = r.id
     AND b.status = 'active'
     AND b.borrow_date <= CURRENT_TIMESTAMP
     AND b.return_date >= CURRENT_TIMESTAMP
     LIMIT 1) AS current_borrowing_id,
     
    (SELECT b.borrower_name 
     FROM borrowings b
     WHERE b.room_id = r.id
     AND b.status = 'active'
     AND b.borrow_date <= CURRENT_TIMESTAMP
     AND b.return_date >= CURRENT_TIMESTAMP
     LIMIT 1) AS current_borrower,
     
    (SELECT b.return_date 
     FROM borrowings b
     WHERE b.room_id = r.id
     AND b.status = 'active'
     AND b.borrow_date <= CURRENT_TIMESTAMP
     AND b.return_date >= CURRENT_TIMESTAMP
     LIMIT 1) AS current_booking_until,
    
    -- Next upcoming booking
    (SELECT MIN(b.borrow_date)
     FROM borrowings b
     WHERE b.room_id = r.id
     AND b.status = 'active'
     AND b.borrow_date > CURRENT_TIMESTAMP) AS next_booking_date,
    
    -- Statistics
    (SELECT COUNT(*) 
     FROM borrowings b
     WHERE b.room_id = r.id) AS total_booking_count,
     
    (SELECT COUNT(*) 
     FROM borrowings b
     WHERE b.room_id = r.id
     AND b.status = 'active') AS active_booking_count

FROM public.rooms r;

COMMENT ON VIEW public.room_availability_view IS 'Real-time room availability with current and upcoming bookings';

-- View 4: Borrowing History with Details
-- Purpose: Complete borrowing history for reporting
DROP VIEW IF EXISTS public.borrowing_history_view CASCADE;
CREATE VIEW public.borrowing_history_view AS
SELECT 
    b.id,
    b.borrower_name,
    b.occupation,
    b.type,
    b.borrow_date,
    b.return_date,
    b.status,
    b.created_at,
    
    -- Item details
    CASE 
        WHEN b.type = 'room' THEN r.name
        WHEN b.type = 'equipment' THEN 
            (SELECT STRING_AGG(e.name || ' (x' || be.quantity || ')', ', ' ORDER BY e.name)
             FROM borrowing_equipment be
             JOIN equipment e ON be.equipment_id = e.id
             WHERE be.borrowing_id = b.id)
    END AS item_details,
    
    -- Additional info
    CASE 
        WHEN b.type = 'room' THEN r.description
        ELSE NULL
    END AS item_description,
    
    -- Status flags
    b.status = 'returned' AS is_returned,
    b.status = 'active' AND b.return_date < CURRENT_TIMESTAMP AS is_overdue,
    
    -- Duration
    CASE 
        WHEN b.status = 'returned' THEN 
            EXTRACT(EPOCH FROM (b.updated_at - b.borrow_date))/86400
        ELSE 
            EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - b.borrow_date))/86400
    END AS actual_duration_days

FROM public.borrowings b
LEFT JOIN public.rooms r ON b.room_id = r.id;

COMMENT ON VIEW public.borrowing_history_view IS 'Complete borrowing history with calculated fields for reporting';

-- ============================================================================
-- STEP 4: Create Functions for Data Integrity
-- ============================================================================

-- Function: Check equipment availability before borrowing
CREATE OR REPLACE FUNCTION check_equipment_availability(
    p_equipment_id INTEGER,
    p_quantity INTEGER
) RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 
        FROM equipment 
        WHERE id = p_equipment_id 
        AND is_active = true 
        AND available_quantity >= p_quantity
    );
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION check_equipment_availability IS 'Check if equipment has sufficient available quantity';

-- Function: Check room availability for time range
CREATE OR REPLACE FUNCTION check_room_availability(
    p_room_id INTEGER,
    p_borrow_date TIMESTAMP,
    p_return_date TIMESTAMP,
    p_exclude_borrowing_id INTEGER DEFAULT NULL
) RETURNS BOOLEAN AS $$
BEGIN
    RETURN NOT EXISTS (
        SELECT 1 
        FROM borrowings
        WHERE room_id = p_room_id
        AND status = 'active'
        AND (id != p_exclude_borrowing_id OR p_exclude_borrowing_id IS NULL)
        AND (
            (borrow_date <= p_borrow_date AND return_date >= p_borrow_date) OR
            (borrow_date <= p_return_date AND return_date >= p_return_date) OR
            (borrow_date >= p_borrow_date AND return_date <= p_return_date)
        )
    );
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION check_room_availability IS 'Check if room is available for specified time range';

-- ============================================================================
-- STEP 5: Create Triggers for Automatic Updates
-- ============================================================================

-- Trigger: Update borrowings.updated_at on modification
CREATE OR REPLACE FUNCTION update_borrowing_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_update_borrowing_timestamp ON public.borrowings;
CREATE TRIGGER trigger_update_borrowing_timestamp
    BEFORE UPDATE ON public.borrowings
    FOR EACH ROW
    EXECUTE FUNCTION update_borrowing_timestamp();

-- Trigger: Automatically update equipment.available_quantity
CREATE OR REPLACE FUNCTION update_equipment_quantity_on_borrowing()
RETURNS TRIGGER AS $$
DECLARE
    v_borrowing_status VARCHAR(20);
BEGIN
    -- Get borrowing status
    SELECT status INTO v_borrowing_status 
    FROM borrowings 
    WHERE id = NEW.borrowing_id;
    
    IF TG_OP = 'INSERT' THEN
        -- Decrease available quantity when borrowing is created
        IF v_borrowing_status = 'active' THEN
            UPDATE equipment 
            SET available_quantity = available_quantity - NEW.quantity
            WHERE id = NEW.equipment_id
            AND available_quantity >= NEW.quantity;
            
            IF NOT FOUND THEN
                RAISE EXCEPTION 'Insufficient equipment quantity available';
            END IF;
        END IF;
        RETURN NEW;
        
    ELSIF TG_OP = 'DELETE' THEN
        -- Increase available quantity when borrowing is deleted or returned
        UPDATE equipment 
        SET available_quantity = LEAST(available_quantity + OLD.quantity, quantity)
        WHERE id = OLD.equipment_id;
        RETURN OLD;
        
    END IF;
    
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_equipment_quantity_on_borrowing ON public.borrowing_equipment;
CREATE TRIGGER trigger_equipment_quantity_on_borrowing
    AFTER INSERT OR DELETE ON public.borrowing_equipment
    FOR EACH ROW
    EXECUTE FUNCTION update_equipment_quantity_on_borrowing();

-- Trigger: Automatically restore equipment quantity when status changes to 'returned'
CREATE OR REPLACE FUNCTION update_equipment_quantity_on_return()
RETURNS TRIGGER AS $$
BEGIN
    -- If status changed from 'active' to 'returned'
    IF OLD.status = 'active' AND NEW.status = 'returned' AND NEW.type = 'equipment' THEN
        -- Restore equipment quantities
        UPDATE equipment e
        SET available_quantity = LEAST(
            e.available_quantity + be.quantity,
            e.quantity
        )
        FROM borrowing_equipment be
        WHERE be.borrowing_id = NEW.id
        AND be.equipment_id = e.id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_equipment_quantity_on_return ON public.borrowings;
CREATE TRIGGER trigger_equipment_quantity_on_return
    AFTER UPDATE ON public.borrowings
    FOR EACH ROW
    WHEN (OLD.status IS DISTINCT FROM NEW.status)
    EXECUTE FUNCTION update_equipment_quantity_on_return();

-- ============================================================================
-- STEP 6: Grant Permissions
-- ============================================================================

-- Grant access to new table and views
GRANT SELECT, INSERT, UPDATE, DELETE ON public.borrowing_equipment TO PUBLIC;
GRANT SELECT ON public.borrowing_details_view TO PUBLIC;
GRANT SELECT ON public.equipment_availability_view TO PUBLIC;
GRANT SELECT ON public.room_availability_view TO PUBLIC;
GRANT SELECT ON public.borrowing_history_view TO PUBLIC;
GRANT USAGE ON SEQUENCE public.borrowing_equipment_id_seq TO PUBLIC;

-- ============================================================================
-- STEP 7: Create Indexes for Performance
-- ============================================================================

-- Additional composite indexes for common queries
CREATE INDEX IF NOT EXISTS idx_borrowings_type_status 
    ON public.borrowings(type, status);

CREATE INDEX IF NOT EXISTS idx_borrowings_status_dates 
    ON public.borrowings(status, borrow_date, return_date);

CREATE INDEX IF NOT EXISTS idx_borrowings_borrower 
    ON public.borrowings(borrower_name);

-- Index for equipment availability queries
CREATE INDEX IF NOT EXISTS idx_equipment_availability 
    ON public.equipment(is_active, available_quantity);

COMMIT;

-- ============================================================================
-- END OF MIGRATION
-- ============================================================================

-- Verification Queries
-- Run these after migration to verify structure:

-- SELECT * FROM borrowing_details_view LIMIT 10;
-- SELECT * FROM equipment_availability_view;
-- SELECT * FROM room_availability_view;
-- SELECT * FROM borrowing_history_view WHERE status = 'active';
