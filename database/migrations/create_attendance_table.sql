-- Tabel untuk absensi mahasiswa
CREATE TABLE IF NOT EXISTS student_attendance (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nim VARCHAR(50) NOT NULL,
    attendance_type VARCHAR(10) NOT NULL CHECK (attendance_type IN ('datang', 'pulang')),
    attendance_date DATE NOT NULL DEFAULT CURRENT_DATE,
    attendance_time TIME NOT NULL DEFAULT CURRENT_TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    CONSTRAINT unique_attendance UNIQUE (nim, attendance_date, attendance_type)
);

-- Index untuk meningkatkan performa query
CREATE INDEX idx_attendance_date ON student_attendance(attendance_date DESC);
CREATE INDEX idx_attendance_nim ON student_attendance(nim);
CREATE INDEX idx_attendance_type ON student_attendance(attendance_type);

-- Komentar untuk dokumentasi
COMMENT ON TABLE student_attendance IS 'Tabel untuk mencatat absensi mahasiswa (datang dan pulang)';
COMMENT ON COLUMN student_attendance.attendance_type IS 'Jenis absensi: datang atau pulang';
COMMENT ON COLUMN student_attendance.nim IS 'Nomor Induk Mahasiswa';
