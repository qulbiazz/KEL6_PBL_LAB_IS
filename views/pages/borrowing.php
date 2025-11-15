<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BorrowingModel.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil input
    $borrower_name = trim($_POST['nama'] ?? '');
    $occupation = trim($_POST['pekerjaan'] ?? '');
    if ($occupation === 'lainnya') {
        $occupation = trim($_POST['pekerjaan_lainnya'] ?? '');
    }
    $type = $_POST['jenis'] ?? '';

    if (isset($_POST['item'])) {
        if (is_array($_POST['item'])) {
            $item_name = implode(', ', $_POST['item']);
        } else {
            $item_name = $_POST['item'];
        }
    } else {
        $item_name = '';
    }

    $borrow_date = trim($_POST['tanggal_pinjam'] ?? '');
    $return_date = trim($_POST['tanggal_kembali'] ?? '');

    // -----------------------------
    // VALIDASI INPUT
    // -----------------------------
    if (empty($borrower_name) || empty($occupation) || empty($type) || empty($item_name) || empty($borrow_date)) {
        $response['message'] = 'Semua field wajib diisi!';

    } elseif (!in_array($type, ['equipment', 'room'])) {
        $response['message'] = 'Tipe peminjaman tidak valid!';

    } elseif (!strtotime($borrow_date)) {
        $response['message'] = 'Format tanggal peminjaman tidak valid!';

    } elseif (!empty($return_date) && !strtotime($return_date)) {
        $response['message'] = 'Format tanggal pengembalian tidak valid!';

    } elseif (!empty($return_date) && strtotime($return_date) < strtotime($borrow_date)) {
        $response['message'] = 'Tanggal pengembalian tidak boleh lebih awal dari tanggal peminjaman!';

    } else {

        // Jika validasi lolos → Insert ke database
        $db = new Database();
        $conn = $db->getConnection();
        $borrowModel = new BorrowingModel($conn);

        $data = [
            'borrower_name' => $borrower_name,
            'occupation' => $occupation,
            'type' => $type,
            'item_name' => $item_name,
            'borrow_date' => $borrow_date,
            'return_date' => $return_date
        ];

        if ($borrowModel->createBorrowing($data)) {
            $response['success'] = true;
            $response['message'] = 'Peminjaman berhasil dicatat!';
        } else {
            $response['message'] = 'Gagal menyimpan data peminjaman!';
        }
    }

    // ---------------------------------------
    // 🔥 SELALU KEMBALIKAN JSON UNTUK AJAX
    // ---------------------------------------
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
    <title>Peminjaman Barang - InLet</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Tema opsional (bisa ganti ke "material_blue", "dark", dll) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

</head>

<body class="bg-gray-50">

    <div class="container mx-auto px-4 py-8 mt-20">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-tools text-4xl text-blue-600"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Peminjaman Alat</h1>
                <p class="text-gray-600">Silakan isi form di bawah untuk melakukan Peminjaman</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertMessage" class="hidden mb-6"></div>

            <?php if (!empty($response['message']) && !isset($_POST['ajax'])): ?>
                <div
                    class="mb-6 p-4 rounded-lg <?php echo $response['success'] ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
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
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-briefcase mr-2 text-blue-600"></i>Perkerjaan *
                        </label>
                        <div class="flex items-center">
                            <input id="dosen-pekerjaan" type="radio" value="dosen" name="pekerjaan"
                                class="w-4 h-4 block text-gray-700 font-semibold mb-2 mr-2">
                            <label for="dosen-pekerjaan" class="block text-gray-700 font-semibold mb-2">Dosen</label>
                        </div>
                        <div class="flex items-center">
                            <input id="mahasiswa-pekerjaan" type="radio" value="mahasiswa" name="pekerjaan"
                                class="w-4 h-4 block text-gray-700 font-semibold mb-2 mr-2">
                            <label for="mahasiswa-pekerjaan"
                                class="block text-gray-700 font-semibold mb-2">Mahasiswa</label>
                        </div>
                        <div class="flex items-center">
                            <input id="lainnya-pekerjaan" type="radio" value="lainnya" name="pekerjaan"
                                class="w-4 h-4 block text-gray-700 font-semibold mb-2 mr-2">
                            <label for="lainnya-pekerjaan"
                                class="block text-gray-700 font-semibold mb-2">Lainnya</label>
                        </div>
                        <div id="input-lainnya" class="mt-3 hidden">
                            <input type="text" id="pekerjaan-lainnya" name="pekerjaan_lainnya"
                                placeholder="Masukkan pekerjaan lainnya"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Dropdown Alat/Ruang -->
                    <div class="mb-6">
                        <label for="jenis-peminjaman" name="jenis" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-box mr-2 text-blue-600"></i>Jenis Peminjaman *
                        </label>

                        <select id="jenis-peminjaman"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            name="jenis">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="equipment">Alat</option>
                            <option value="room">Ruang</option>
                        </select>
                    </div>

                    <!-- Pilihan ruang (single select) -->
                    <div id="pilihan-ruang" class="mb-6 hidden">
                        <label for="ruang" class="block text-gray-700 font-semibold mb-2">Pilih Ruang</label>
                        <select id="ruang" name="item"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Ruang --</option>
                            <option value="Ruang Komputer">Ruang Komputer</option>
                            <option value="Ruang Meeting">Ruang Meeting</option>
                            <option value="Ruang Multimedia">Ruang Multimedia</option>
                        </select>
                    </div>

                    <!-- Pilihan Alat (Checkbox Multiple) -->
                    <div id="pilihan-alat" class="mb-6 hidden">
                        <label class="block text-gray-700 font-semibold mb-2">
                            Pilih Alat (bisa lebih dari 1)
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="item[]" value="Laptop"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                Laptop
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="item[]" value="Proyektor"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                Proyektor
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="item[]" value="Kamera"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                Kamera
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="item[]" value="Tripod"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                Tripod
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="item[]" value="Microphone"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                                Microphone
                            </label>
                        </div>
                    </div>
                    <!-- Form Kalender Rentang Peminjaman -->
                    <div class="mb-6">
                        <label for="tanggal-peminjaman" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Tanggal Peminjaman *
                        </label>

                        <input id="tanggal-peminjaman" name="tanggal_pinjam"
                            placeholder="Pilih rentang tanggal peminjaman"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>

                        <p id="tanggal-info" class="text-sm text-gray-500 mt-2 hidden"></p>
                    </div>
                    <!-- Tanggal Kembali (otomatis dari rentang terakhir) -->
                    <div class="mb-6">
                        <label for="tanggal-kembali" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-undo-alt mr-2 text-blue-600"></i>Tanggal Kembali
                        </label>

                        <input id="tanggal-kembali" name="tanggal_kembali" type="text" readonly
                            class="w-full px-4 py-2 border border-gray-300 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tanggal kembali otomatis diisi">
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
            const tanggalPeminjaman = document.getElementById("tanggal-peminjaman");
            const tanggalKembali = document.getElementById("tanggal-kembali");
            const info = document.getElementById("tanggal-info");

            // Inisialisasi Flatpickr
            flatpickr(tanggalPeminjaman, {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "id",
                onChange: function (selectedDates, dateStr) {
                    if (selectedDates.length === 2) {
                        const start = selectedDates[0];
                        const end = selectedDates[1];

                        // Hitung lama hari
                        const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

                        // Tampilkan info lama peminjaman
                        info.textContent = `Lama peminjaman: ${diffDays} hari (${dateStr})`;
                        info.classList.remove("hidden");

                        // Format tanggal kembali manual tanpa timezone bug
                        const year = end.getFullYear();
                        const month = String(end.getMonth() + 1).padStart(2, '0');
                        const day = String(end.getDate()).padStart(2, '0');

                        tanggalKembali.value = `${year}-${month}-${day}`;
                    } else {
                        info.classList.add("hidden");
                        tanggalKembali.value = "";
                    }
                }
            });
        });
    </script>


    <script>
        // AJAX Form Submission
        document.getElementById('borrowingForm').addEventListener('submit', function (e) {
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
    <script>
        // Ambil elemen radio dan input tambahan
        const radioLainnya = document.getElementById("lainnya-pekerjaan");
        const inputLainnya = document.getElementById("input-lainnya");
        const radioSemua = document.querySelectorAll('input[name="pekerjaan"]');

        // Saat salah satu radio diklik
        radioSemua.forEach(radio => {
            radio.addEventListener("change", () => {
                if (radioLainnya.checked) {
                    inputLainnya.classList.remove("hidden");
                } else {
                    inputLainnya.classList.add("hidden");
                }
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // === Radio "Lainnya" ===
            const radioLainnya = document.getElementById("lainnya-peminjaman");
            const inputLainnya = document.getElementById("input-lainnya");
            const radioSemua = document.querySelectorAll('input[name="peminjaman"]');

            radioSemua.forEach(radio => {
                radio.addEventListener("change", () => {
                    inputLainnya.classList.toggle("hidden", !radioLainnya.checked);
                });
            });

            // === Dropdown Alat / Ruang ===
            const jenisPeminjaman = document.getElementById("jenis-peminjaman");
            const pilihanRuang = document.getElementById("pilihan-ruang");
            const pilihanAlat = document.getElementById("pilihan-alat");

            if (jenisPeminjaman) {
                jenisPeminjaman.addEventListener("change", () => {
                    if (jenisPeminjaman.value === "room") {
                        pilihanRuang.classList.remove("hidden");
                        pilihanAlat.classList.add("hidden");
                    } else if (jenisPeminjaman.value === "equipment") {
                        pilihanAlat.classList.remove("hidden");
                        pilihanRuang.classList.add("hidden");
                    } else {
                        pilihanRuang.classList.add("hidden");
                        pilihanAlat.classList.add("hidden");
                    }
                });
            }
        });
    </script>


</body>

</html>