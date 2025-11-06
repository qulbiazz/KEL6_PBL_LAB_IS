-- Script untuk membuat atau mereset admin user
-- Jalankan script ini jika tidak bisa login

-- Hapus admin user lama jika ada (opsional)
-- DELETE FROM admin_users WHERE username = 'admin';

-- Buat admin user baru dengan password 'admin123'
-- Password hash dibuat dengan: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admin_users (username, password, email, full_name)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@inlet.com',
    'Administrator'
)
ON CONFLICT (username) DO UPDATE 
SET 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    email = 'admin@inlet.com',
    full_name = 'Administrator';

-- Verifikasi
SELECT id, username, email, full_name, created_at FROM admin_users WHERE username = 'admin';