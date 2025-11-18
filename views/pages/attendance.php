<?php
// Load students for dropdown
require_once __DIR__ . '/../../models/StudentModel.php';
$studentModel = new StudentModel($conn);
$students = $studentModel->getActiveStudents();

// For non-AJAX requests (page reload with form submission)
$response = ['success' => false, 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    require_once __DIR__ . '/../../models/AttendanceModel.php';
    
    $student_id = (int)($_POST['student_id'] ?? 0);
    $attendance_type = $_POST['attendance_type'] ?? '';

    // Validasi input
    if (empty($student_id) || empty($attendance_type)) {
        $response['message'] = 'Semua field harus diisi!';
    } elseif (!in_array($attendance_type, ['datang', 'pulang'])) {
        $response['message'] = 'Tipe absensi tidak valid!';
    } else {
        $attendanceModel = new AttendanceModel($conn);

        // Ambil IP address dan user agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Catat absensi
        $response = $attendanceModel->recordAttendance(
            $student_id,
            $attendance_type,
            $ip_address,
            $user_agent
        );
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
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
                <!-- Pilih Mahasiswa -->
                <div class="mb-6">
                    <label for="student_id" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-user-graduate mr-2 text-blue-600"></i>Pilih Mahasiswa *
                    </label>
                    <select
                        id="student_id"
                        name="student_id"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                        style="width: 100%;">
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= htmlspecialchars($student['nim']) ?> - <?= htmlspecialchars($student['name']) ?>
                                <?php if (!empty($student['program_study'])): ?>
                                    (<?= htmlspecialchars($student['program_study']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ketik untuk mencari nama atau NIM mahasiswa. Jika tidak ada, hubungi admin.
                    </p>
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
                                class="peer sr-only">
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
                                class="peer sr-only">
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
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center text-lg shadow-lg hover:shadow-xl">
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
                        <li>Pilih nama Anda dari dropdown di atas</li>
                        <li>Absen datang hanya bisa dilakukan sekali per hari</li>
                        <li>Absen pulang hanya bisa dilakukan setelah absen datang</li>
                        <li>Jika nama tidak ada, hubungi admin untuk pendaftaran</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom styling for Select2 to match our design */
    .select2-container--default .select2-selection--single {
        height: 50px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem 1rem !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 0 !important;
        color: #374151 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
        right: 8px !important;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }
    
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 0.5rem !important;
    }
    
    .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #dbeafe !important;
        color: #1e40af !important;
    }
</style>

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Initialize Select2 with search
    $(document).ready(function() {
        $('#student_id').select2({
            placeholder: '-- Pilih Mahasiswa --',
            allowClear: true,
            language: {
                noResults: function() {
                    return "Mahasiswa tidak ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                },
                inputTooShort: function() {
                    return "Ketik untuk mencari";
                }
            },
            width: '100%'
        });
    });

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

        fetch('index.php?page=attendance', {
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
                alertDiv.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

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
