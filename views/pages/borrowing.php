<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BorrowingModel.php';
require_once __DIR__ . '/../../models/EquipmentModel.php';

$response = ['success' => false, 'message' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Ambil input
        $borrower_name = trim($_POST['nama'] ?? '');
        $occupation = trim($_POST['pekerjaan'] ?? '');
        if ($occupation === 'lainnya') {
            $occupation = trim($_POST['pekerjaan_lainnya'] ?? '');
        }
        $type = $_POST['jenis'] ?? '';
        $borrow_date = trim($_POST['waktu_pinjam'] ?? '');
        $return_date = trim($_POST['waktu_kembali'] ?? '');

        // Handle item based on type
        $item_name = '';
        $item_id = null;
        
        if ($type === 'room') {
            $item_name = trim($_POST['nama_ruang'] ?? '');
            if (empty($item_name)) {
                throw new Exception('Nama ruangan wajib diisi!');
            }
        } elseif ($type === 'equipment') {
            if (isset($_POST['alat']) && is_array($_POST['alat'])) {
                $item_ids = $_POST['alat'];
                if (empty($item_ids)) {
                    throw new Exception('Minimal pilih 1 alat!');
                }
                // For equipment, store as comma-separated IDs in item_name
                $item_id = null; // Multiple items, no single ID
                $item_name = implode(',', $item_ids);
            } else {
                throw new Exception('Alat wajib dipilih!');
            }
        }

        // -----------------------------
        // VALIDASI INPUT
        // -----------------------------
        if (empty($borrower_name)) {
            $response['message'] = 'Nama lengkap wajib diisi!';

        } elseif (empty($occupation)) {
            $response['message'] = 'Pekerjaan wajib diisi!';

        } elseif (empty($type)) {
            $response['message'] = 'Jenis peminjaman wajib dipilih!';

        } elseif (!in_array($type, ['equipment', 'room'])) {
            $response['message'] = 'Tipe peminjaman tidak valid!';

        } elseif (empty($borrow_date)) {
            $response['message'] = 'Waktu peminjaman wajib diisi!';

        } elseif (!strtotime($borrow_date)) {
            $response['message'] = 'Format waktu peminjaman tidak valid!';

        } elseif (empty($return_date)) {
            $response['message'] = 'Waktu pengembalian wajib diisi!';

        } elseif (!strtotime($return_date)) {
            $response['message'] = 'Format waktu pengembalian tidak valid!';

        } elseif (strtotime($return_date) <= strtotime($borrow_date)) {
            $response['message'] = 'Waktu pengembalian harus lebih dari waktu peminjaman!';

        } else {

            // Jika validasi lolos → Insert ke database
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                throw new Exception('Gagal terhubung ke database');
            }
            
            $borrowModel = new BorrowingModel($conn);
            $equipmentModel = new EquipmentModel($conn);
            
            // Additional validation for room - no availability check needed
            if ($type === 'room') {
                // Room name is already validated above
            } else {
                // Get equipment names
                $equipmentNames = [];
                foreach (explode(',', $item_name) as $eqId) {
                    $eqData = $equipmentModel->getEquipmentById($eqId);
                    if ($eqData) {
                        $equipmentNames[] = $eqData['name'];
                    }
                }
                $item_name = implode(', ', $equipmentNames);
            }

            $data = [
                'borrower_name' => $borrower_name,
                'occupation' => $occupation,
                'type' => $type,
                'item_name' => $item_name,
                'item_id' => $item_id,
                'item_type' => $type,
                'borrow_date' => $borrow_date,
                'return_date' => $return_date,
                'status' => 'active'
            ];

            if ($borrowModel->createBorrowing($data)) {
                $response['success'] = true;
                $response['message'] = 'Peminjaman berhasil dicatat!';
            } else {
                $response['message'] = 'Gagal menyimpan data peminjaman!';
            }
        }
        
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }

    // Return JSON for AJAX request
    if (isset($_POST['ajax'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Load equipment for form display
try {
    $db = new Database();
    $conn = $db->getConnection();
    $equipmentModel = new EquipmentModel($conn);
    
    $equipments = $equipmentModel->getAvailableEquipment();
} catch (Exception $e) {
    $equipments = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat & Ruang - InLet</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="bg-gray-50">

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-calendar-check text-4xl text-blue-600"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Peminjaman Alat & Ruang</h1>
                <p class="text-gray-600">Silakan isi form di bawah untuk melakukan peminjaman</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertMessage" class="hidden mb-6"></div>

            <?php if (!empty($response['message']) && !isset($_POST['ajax'])): ?>
                <div
                    class="mb-6 p-4 rounded-lg <?php echo $response['success'] ? 'bg-green-100 border-green-400 text-green-700 border' : 'bg-red-100 border-red-400 text-red-700 border'; ?>">
                    <div class="flex items-center">
                        <i
                            class="fas <?php echo $response['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                        <span><?php echo htmlspecialchars($response['message']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <form id="borrowingForm" method="POST" action="">
                    <!-- Nama Lengkap -->
                    <div class="mb-6">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Nama Lengkap *
                        </label>
                        <input type="text" id="nama" name="nama" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Pekerjaan -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-briefcase mr-2 text-blue-600"></i>Pekerjaan *
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" value="Dosen" name="pekerjaan" required
                                    class="w-4 h-4 text-blue-600 mr-3">
                                <span>Dosen</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" value="Mahasiswa" name="pekerjaan" required
                                    class="w-4 h-4 text-blue-600 mr-3">
                                <span>Mahasiswa</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input id="lainnya-pekerjaan" type="radio" value="lainnya" name="pekerjaan" required
                                    class="w-4 h-4 text-blue-600 mr-3">
                                <span>Lainnya</span>
                            </label>
                        </div>
                        <div id="input-lainnya" class="mt-3 hidden">
                            <input type="text" id="pekerjaan-lainnya" name="pekerjaan_lainnya"
                                placeholder="Masukkan pekerjaan lainnya"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Jenis Peminjaman -->
                    <div class="mb-6">
                        <label for="jenis-peminjaman" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-box mr-2 text-blue-600"></i>Jenis Peminjaman *
                        </label>
                        <select id="jenis-peminjaman" name="jenis" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="equipment">Alat</option>
                            <option value="room">Ruang</option>
                        </select>
                    </div>

                    <!-- Waktu Peminjaman -->
                    <div class="mb-6">
                        <label for="waktu-pinjam" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Waktu Peminjaman *
                        </label>
                        <input type="text" id="waktu-pinjam" name="waktu_pinjam" required
                            placeholder="Pilih tanggal dan waktu peminjaman"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Waktu Pengembalian -->
                    <div class="mb-6">
                        <label for="waktu-kembali" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-undo-alt mr-2 text-blue-600"></i>Waktu Pengembalian *
                        </label>
                        <input type="text" id="waktu-kembali" name="waktu_kembali" required
                            placeholder="Pilih tanggal dan waktu pengembalian"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Keperluan Ruang (initially hidden) -->
                    <div id="pilihan-ruang" class="mb-6 hidden">
                        <label for="keperluan-ruang" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Keperluan Peminjaman Lab Inlet *
                        </label>
                        <textarea id="keperluan-ruang" name="keperluan_ruang" rows="3"
                            placeholder="Masukkan tujuan/keperluan peminjaman Lab Inlet (contoh: Praktikum Jaringan Komputer, Rapat Organisasi, dll)"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        
                        <!-- Room availability indicator -->
                        <div id="room-availability-indicator" class="mt-3 hidden">
                            <div id="room-available" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span class="text-sm font-medium">Lab Inlet tersedia untuk waktu yang dipilih</span>
                                </div>
                            </div>
                            <div id="room-unavailable" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start text-red-700">
                                    <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                                    <div class="text-sm">
                                        <span class="font-medium block mb-1">Lab Inlet sudah dipinjam pada tanggal dan waktu tersebut</span>
                                        <span class="text-xs">Silahkan pilih tanggal dan waktu lainnya!</span>
                                    </div>
                                </div>
                            </div>
                            <div id="room-checking" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center text-blue-700">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    <span class="text-sm">Memeriksa ketersediaan...</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Peminjaman untuk Lab Inlet
                        </p>
                    </div>

                    <!-- Pilihan Alat (initially hidden) -->
                    <div id="pilihan-alat" class="mb-6 hidden">
                        <label class="block text-gray-700 font-semibold mb-3">
                            <i class="fas fa-tools mr-2 text-blue-600"></i>Pilih Alat (bisa lebih dari 1) *
                        </label>
                        <div id="equipment-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <p class="text-gray-500 col-span-2 text-center py-4">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data alat...
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center text-lg shadow-lg hover:shadow-xl">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Submit Peminjaman</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // console.log("DOM Loaded - Initializing borrowing form");
            
            const waktuPinjam = document.getElementById("waktu-pinjam");
            const waktuKembali = document.getElementById("waktu-kembali");
            const jenisPeminjaman = document.getElementById("jenis-peminjaman");
            const pilihanRuang = document.getElementById("pilihan-ruang");
            const pilihanAlat = document.getElementById("pilihan-alat");
            const keperluanRuang = document.getElementById("keperluan-ruang");
            const equipmentContainer = document.getElementById("equipment-container");
            const roomAvailabilityIndicator = document.getElementById("room-availability-indicator");
            const roomAvailable = document.getElementById("room-available");
            const roomUnavailable = document.getElementById("room-unavailable");
            const roomChecking = document.getElementById("room-checking");

            let borrowDatePicker, returnDatePicker;
            let isRoomAvailable = true; // Track room availability

            // Inisialisasi Flatpickr untuk waktu peminjaman
            borrowDatePicker = flatpickr(waktuPinjam, {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minDate: "today",
                locale: "id",
                onChange: function (selectedDates) {
                    // Set minDate untuk waktu pengembalian
                    if (selectedDates[0]) {
                        returnDatePicker.set('minDate', selectedDates[0]);
                    }
                    // Check room availability if room is selected and both dates are filled
                    if (jenisPeminjaman.value === 'room' && waktuPinjam.value && waktuKembali.value) {
                        checkRoomAvailability();
                    }
                }
            });

            // Inisialisasi Flatpickr untuk waktu pengembalian
            returnDatePicker = flatpickr(waktuKembali, {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minDate: "today",
                locale: "id",
                onChange: function (selectedDates) {
                    // Check room availability if room is selected and both dates are filled
                    if (jenisPeminjaman.value === 'room' && waktuPinjam.value && waktuKembali.value) {
                        checkRoomAvailability();
                    }
                }
            });

            // Handle radio "Lainnya"
            const radioLainnya = document.getElementById("lainnya-pekerjaan");
            const inputLainnya = document.getElementById("input-lainnya");
            const radioSemua = document.querySelectorAll('input[name="pekerjaan"]');

            radioSemua.forEach(radio => {
                radio.addEventListener("change", () => {
                    inputLainnya.classList.toggle("hidden", !radioLainnya.checked);
                });
            });

            // Handle dropdown Alat/Ruang
            jenisPeminjaman.addEventListener("change", () => {
                // console.log("Jenis peminjaman changed to:", jenisPeminjaman.value);
                
                if (jenisPeminjaman.value === "room") {
                    // console.log("Showing room input");
                    pilihanRuang.classList.remove("hidden");
                    pilihanAlat.classList.add("hidden");
                    // Set required attribute on keperluan input
                    if (keperluanRuang) keperluanRuang.required = true;
                    // Check room availability if dates are selected
                    if (waktuPinjam.value && waktuKembali.value) {
                        checkRoomAvailability();
                    }
                } else if (jenisPeminjaman.value === "equipment") {
                    // console.log("Showing equipment selection");
                    pilihanAlat.classList.remove("hidden");
                    pilihanRuang.classList.add("hidden");
                    // Remove required from keperluan
                    if (keperluanRuang) keperluanRuang.required = false;
                    // Hide room availability indicator
                    if (roomAvailabilityIndicator) roomAvailabilityIndicator.classList.add("hidden");
                    
                    // Load equipment
                    // console.log("Loading equipment...");
                    loadEquipment();
                } else {
                    // console.log("No selection, hiding both");
                    pilihanRuang.classList.add("hidden");
                    pilihanAlat.classList.add("hidden");
                    if (keperluanRuang) keperluanRuang.required = false;
                    if (roomAvailabilityIndicator) roomAvailabilityIndicator.classList.add("hidden");
                }
            });

            // Function to check room availability
            function checkRoomAvailability() {
                const borrowDate = waktuPinjam.value;
                const returnDate = waktuKembali.value;

                if (!borrowDate || !returnDate) {
                    return;
                }

                // Show checking indicator
                roomAvailabilityIndicator.classList.remove('hidden');
                roomAvailable.classList.add('hidden');
                roomUnavailable.classList.add('hidden');
                roomChecking.classList.remove('hidden');

                // Check availability via AJAX
                fetch(`views/pages/ajax_check_room_availability.php?borrow_date=${encodeURIComponent(borrowDate)}&return_date=${encodeURIComponent(returnDate)}`)
                    .then(response => response.json())
                    .then(data => {
                        roomChecking.classList.add('hidden');
                        
                        if (data.available) {
                            roomAvailable.classList.remove('hidden');
                            roomUnavailable.classList.add('hidden');
                            isRoomAvailable = true;
                        } else {
                            roomAvailable.classList.add('hidden');
                            roomUnavailable.classList.remove('hidden');
                            isRoomAvailable = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking room availability:', error);
                        roomChecking.classList.add('hidden');
                        // Hide indicator on error
                        roomAvailabilityIndicator.classList.add('hidden');
                    });
            }

            // Function to load equipment with availability
            function loadEquipment() {
                // console.log("loadEquipment() called");
                // console.log("equipmentContainer:", equipmentContainer);
                
                // Show loading
                equipmentContainer.innerHTML = '<p class="text-gray-500 col-span-2 text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data alat...</p>';

                // console.log("Fetching from: views/pages/ajax_check_equipment.php");
                
                // Fetch equipment
                fetch(`views/pages/ajax_check_equipment.php`)
                    .then(response => {
                        // console.log("Response received:", response);
                        return response.json();
                    })
                    .then(data => {
                        // console.log('Equipment data:', data);
                        if (data.success && data.equipment) {
                            if (data.equipment.length === 0) {
                                equipmentContainer.innerHTML = '<p class="text-gray-500 col-span-2 text-center py-4">Tidak ada alat tersedia</p>';
                            } else {
                                equipmentContainer.innerHTML = '';
                                data.equipment.forEach(equipment => {
                                    const isAvailable = equipment.is_active && equipment.available_quantity > 0;
                                    const label = document.createElement('label');
                                    label.className = `flex items-center p-3 border rounded-lg transition ${isAvailable ? 'border-gray-200 hover:bg-blue-50 cursor-pointer' : 'border-gray-300 bg-gray-100 cursor-not-allowed opacity-60'}`;
                                    
                                    label.innerHTML = `
                                        <input type="checkbox" name="alat[]" value="${equipment.id}" ${!isAvailable ? 'disabled' : ''}
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-3">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div class="font-medium text-gray-800">${equipment.name}</div>
                                                ${!isAvailable ? '<span class="text-xs text-red-600 font-semibold"><i class="fas fa-times-circle mr-1"></i>Tidak Tersedia</span>' : `<span class="text-xs text-green-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>Tersedia: ${equipment.available_quantity}/${equipment.quantity}</span>`}
                                            </div>
                                            ${equipment.description ? `<div class="text-xs text-gray-500 mt-1">${equipment.description}</div>` : ''}
                                        </div>
                                    `;
                                    equipmentContainer.appendChild(label);
                                });
                            }
                        } else {
                            equipmentContainer.innerHTML = `<p class="text-red-500 col-span-2 text-center py-4">${data.message || 'Gagal memuat data alat'}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        equipmentContainer.innerHTML = '<p class="text-red-500 col-span-2 text-center py-4">Terjadi kesalahan saat memuat data alat</p>';
                    });
            }

            // AJAX Form Submission dengan Validasi
            document.getElementById('borrowingForm').addEventListener('submit', function (e) {
                e.preventDefault();

                // Validasi Frontend
                const nama = document.getElementById('nama').value.trim();
                const pekerjaan = document.querySelector('input[name="pekerjaan"]:checked');
                const jenis = document.getElementById('jenis-peminjaman').value;
                const waktuPinjamVal = document.getElementById('waktu-pinjam').value;
                const waktuKembaliVal = document.getElementById('waktu-kembali').value;

                // Cek nama
                if (!nama) {
                    showAlert('Nama lengkap wajib diisi!', false);
                    return;
                }

                // Cek pekerjaan
                if (!pekerjaan) {
                    showAlert('Pekerjaan wajib dipilih!', false);
                    return;
                }

                // Cek pekerjaan lainnya jika dipilih
                if (pekerjaan.value === 'lainnya') {
                    const pekerjaanLainnya = document.getElementById('pekerjaan-lainnya').value.trim();
                    if (!pekerjaanLainnya) {
                        showAlert('Pekerjaan lainnya wajib diisi!', false);
                        return;
                    }
                }

                // Cek jenis peminjaman
                if (!jenis) {
                    showAlert('Jenis peminjaman wajib dipilih!', false);
                    return;
                }

                // Cek waktu peminjaman
                if (!waktuPinjamVal) {
                    showAlert('Waktu peminjaman wajib diisi!', false);
                    return;
                }

                // Cek waktu pengembalian
                if (!waktuKembaliVal) {
                    showAlert('Waktu pengembalian wajib diisi!', false);
                    return;
                }

                // Cek item berdasarkan jenis
                if (jenis === 'equipment') {
                    const checkboxes = document.querySelectorAll('input[name="alat[]"]:checked');
                    if (checkboxes.length === 0) {
                        showAlert('Minimal pilih 1 alat!', false);
                        return;
                    }
                } else if (jenis === 'room') {
                    const keperluanRuang = document.getElementById('keperluan-ruang').value.trim();
                    if (!keperluanRuang) {
                        showAlert('Keperluan peminjaman wajib diisi!', false);
                        return;
                    }

                    // Cek ketersediaan ruang sebelum submit
                    if (!isRoomAvailable) {
                        showAlert('Lab Inlet sudah dipinjam pada tanggal dan waktu tersebut. Silahkan pilih waktu dan tanggal yang lain.', false);
                        return;
                    }
                }

                const formData = new FormData(this);
                formData.append('ajax', '1');

                // Disable button
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Memproses...</span>';

                fetch('views/pages/ajax_submit_borrowing.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        return response.clone().text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                // console.log('Raw response:', text);
                                throw new Error('Server mengembalikan response yang bukan JSON');
                            }
                        });
                    })
                    .then(data => {
                        // console.log('Parsed data:', data);
                        showAlert(data.message, data.success);

                        // Reset form if success
                        if (data.success) {
                            this.reset();
                            // Reset hidden divs
                            document.getElementById('pilihan-alat').classList.add('hidden');
                            document.getElementById('pilihan-ruang').classList.add('hidden');
                            document.getElementById('input-lainnya').classList.add('hidden');
                            
                            // Clear flatpickr
                            borrowDatePicker.clear();
                            returnDatePicker.clear();
                        }

                        // Enable button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showAlert(error.message || 'Terjadi kesalahan. Silakan coba lagi.', false);

                        // Enable button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Helper function untuk menampilkan alert
            function showAlert(message, isSuccess) {
                const alertDiv = document.getElementById('alertMessage');
                alertDiv.className = `mb-6 p-4 rounded-lg ${isSuccess ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;
                alertDiv.classList.remove('hidden');
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>

</body>

</html>
