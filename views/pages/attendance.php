<?php
require_once '../../config/database.php';
require_once '../../models/AttendanceModel.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $attendance_type = $_POST['attendance_type'] ?? '';

    // Validasi input
    if (empty($nama) || empty($nim) || empty($attendance_type)) {
        $response['message'] = 'Semua field harus diisi!';
    } elseif (!in_array($attendance_type, ['datang', 'pulang'])) {
        $response['message'] = 'Tipe absensi tidak valid!';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        $attendanceModel = new AttendanceModel($conn);

        // Ambil IP address dan user agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Catat absensi
        $response = $attendanceModel->recordAttendance(
            $nama, 
            $nim, 
            $attendance_type, 
            $ip_address, 
            $user_agent
        );
    }

    // Return JSON response untuk AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mahasiswa - InLet</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include '../layouts/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-20">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-clipboard-check text-4xl text-blue-600"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Absensi Mahasiswa</h1>
                <p class="text-gray-600">Silakan isi form di bawah untuk melakukan absensi</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertMessage" class="hidden mb-6"></div>

            <?php if (!empty($response['message']) && !isset($_POST['ajax'])): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $response['success'] ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                    <div class="flex items-center">
                        <i class="fas <?php echo $response['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                        <span><?php echo htmlspecialchars($response['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <form id="attendanceForm" method="POST" action="">
                    <!-- Nama Lengkap -->
                    <div class="mb-6">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Nama Lengkap *
                        </label>
                        <input 
                            type="text" 
                            id="nama" 
                            name="nama" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            placeholder="Masukkan nama lengkap"
                        >
                    </div>

                    <!-- NIM -->
                    <div class="mb-6">
                        <label for="nim" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-id-card mr-2 text-blue-600"></i>NIM (Nomor Induk Mahasiswa) *
                        </label>
                        <input 
                            type="text" 
                            id="nim" 
                            name="nim" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            placeholder="Masukkan NIM"
                        >
                    </div>

                    <!-- Tipe Absensi -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-clock mr-2 text-blue-600"></i>Tipe Absensi *
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Datang -->
                            <label class="relative cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="attendance_type" 
                                    value="datang" 
                                    required
                                    class="peer sr-only"
                                >
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-arrow-right text-3xl text-green-600"></i>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-700">Absen Datang</span>
                                        <span class="text-sm text-gray-500 mt-1">Check In</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Pulang -->
                            <label class="relative cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="attendance_type" 
                                    value="pulang" 
                                    required
                                    class="peer sr-only"
                                >
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-arrow-left text-3xl text-red-600"></i>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-700">Absen Pulang</span>
                                        <span class="text-sm text-gray-500 mt-1">Check Out</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Info waktu real-time -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-center text-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <span class="text-sm text-gray-700">
                                Waktu saat ini: <span id="currentTime" class="font-semibold text-blue-600"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center text-lg shadow-lg hover:shadow-xl"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Submit Absensi</span>
                    </button>
                </form>
            </div>

            <!-- Info Card -->
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                    <div class="text-sm text-gray-700">
                        <p class="font-semibold mb-1">Perhatian:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pastikan data yang diisi sudah benar</li>
                            <li>Absen datang hanya bisa dilakukan sekali per hari</li>
                            <li>Absen pulang hanya bisa dilakukan setelah absen datang</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../layouts/footer.php'; ?>

    <script>
        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('currentTime').textContent = now.toLocaleString('id-ID', options);
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // AJAX Form Submission
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('ajax', '1');
            
            // Disable button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Memproses...</span>';
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.getElementById('alertMessage');
                alertDiv.className = `mb-6 p-4 rounded-lg ${data.success ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        <span>${data.message}</span>
                    </div>
                `;
                alertDiv.classList.remove('hidden');
                
                // Scroll to alert
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Reset form if success
                if (data.success) {
                    this.reset();
                }
                
                // Enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                const alertDiv = document.getElementById('alertMessage');
                alertDiv.className = 'mb-6 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700';
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>Terjadi kesalahan. Silakan coba lagi.</span>
                    </div>
                `;
                alertDiv.classList.remove('hidden');
                
                // Enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
</body>
</html>
