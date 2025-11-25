-- Tabel untuk master data Alat (Equipment)
CREATE TABLE IF NOT EXISTS public.equipment (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    quantity INTEGER DEFAULT 1,
    available_quantity INTEGER DEFAULT 1,
    display_order INTEGER DEFAULT 999,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk master data Ruangan (Room)
CREATE TABLE IF NOT EXISTS public.rooms (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    capacity INTEGER,
    display_order INTEGER DEFAULT 999,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update tabel borrowings untuk mendukung datetime dan status
ALTER TABLE public.borrowings 
    ALTER COLUMN borrow_date TYPE TIMESTAMP USING borrow_date::timestamp,
    ALTER COLUMN return_date TYPE TIMESTAMP USING return_date::timestamp;

-- Tambah kolom status jika belum ada
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name='borrowings' AND column_name='status') THEN
        ALTER TABLE public.borrowings 
        ADD COLUMN status VARCHAR(20) DEFAULT 'active' 
        CHECK (status IN ('active', 'returned', 'cancelled'));
    END IF;
END $$;

-- Tambah kolom item_id dan item_type untuk referensi ke tabel equipment/rooms
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_name='borrowings' AND column_name='item_id') THEN
        ALTER TABLE public.borrowings 
        ADD COLUMN item_id INTEGER,
        ADD COLUMN item_type VARCHAR(20);
    END IF;
END $$;

-- Insert sample data untuk equipment
INSERT INTO public.equipment (name, description, quantity, available_quantity, display_order) VALUES
('Laptop', 'Laptop untuk presentasi dan kerja', 5, 5, 1),
('Proyektor', 'Proyektor untuk presentasi', 3, 3, 2),
('Kamera', 'Kamera DSLR untuk dokumentasi', 2, 2, 3),
('Tripod', 'Tripod untuk kamera', 4, 4, 4),
('Microphone', 'Microphone wireless untuk acara', 3, 3, 5),
('Speaker', 'Speaker portable untuk acara', 2, 2, 6),
('Whiteboard', 'Whiteboard portable', 3, 3, 7)
ON CONFLICT (name) DO NOTHING;

-- Insert sample data untuk rooms
INSERT INTO public.rooms (name, description, capacity, display_order) VALUES
('Ruang Komputer', 'Lab komputer dengan 30 unit PC', 30, 1),
('Ruang Meeting', 'Ruang meeting lantai 2', 15, 2),
('Ruang Multimedia', 'Ruang multimedia dengan proyektor', 25, 3),
('Aula', 'Aula besar untuk acara', 200, 4),
('Ruang Seminar', 'Ruang seminar lantai 3', 50, 5),
('Lab Jaringan', 'Lab untuk praktik jaringan komputer', 20, 6)
ON CONFLICT (name) DO NOTHING;

-- Tambah index untuk performa
CREATE INDEX IF NOT EXISTS idx_borrowings_dates ON public.borrowings(borrow_date, return_date);
CREATE INDEX IF NOT EXISTS idx_borrowings_status ON public.borrowings(status);
CREATE INDEX IF NOT EXISTS idx_borrowings_type ON public.borrowings(type);
CREATE INDEX IF NOT EXISTS idx_equipment_active ON public.equipment(is_active);
CREATE INDEX IF NOT EXISTS idx_rooms_active ON public.rooms(is_active);
