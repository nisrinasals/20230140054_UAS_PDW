<?php
// admin/beri_nilai.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Keamanan dasar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

// Ambil ID laporan dari URL, jika tidak ada, kembalikan ke halaman daftar laporan
$laporan_id = isset($_GET['laporan_id']) && is_numeric($_GET['laporan_id']) ? intval($_GET['laporan_id']) : 0;
if ($laporan_id === 0) {
    header("Location: laporan_masuk.php");
    exit;
}

// --- LOGIKA UNTUK MENYIMPAN NILAI (PROSES FORM POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_nilai'])) {
    $nilai = $_POST['nilai'];
    $feedback = trim($_POST['feedback_asisten']);
    
    // Validasi sederhana
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $_SESSION['flash_message'] = "Nilai harus berupa angka antara 0 dan 100.";
        $_SESSION['flash_message_type'] = "error";
    } else {
        // Update data di database
        $stmt = $conn->prepare("UPDATE laporan_prak SET nilai = ?, feedback_asisten = ?, status_laporan = 'dinilai', tanggal_dinilai = NOW() WHERE id = ?");
        $stmt->bind_param("dsi", $nilai, $feedback, $laporan_id);
        
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Nilai dan feedback berhasil disimpan.";
            $_SESSION['flash_message_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Gagal menyimpan data.";
            $_SESSION['flash_message_type'] = "error";
        }
        $stmt->close();
    }
    // Redirect kembali ke halaman yang sama untuk refresh data dan mencegah resubmit
    header("Location: beri_nilai.php?laporan_id=$laporan_id");
    exit;
}


// --- PENGAMBILAN DATA UNTUK DITAMPILKAN ---
$sql = "SELECT 
            lp.id, lp.tanggal_upload, lp.status_laporan, lp.nilai, lp.feedback_asisten, lp.file_lapor_path,
            u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
            mp.nama_modul, mp.nomor_modul,
            prak.nama_prak
        FROM 
            laporan_prak AS lp
        JOIN users AS u ON lp.user_id = u.id
        JOIN modul_prak AS mp ON lp.modul_id = mp.id
        JOIN mata_prak AS prak ON mp.mata_prak_id = prak.id
        WHERE lp.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $laporan_id);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Jika laporan dengan ID tersebut tidak ditemukan, kembalikan
if (!$laporan) {
    $_SESSION['flash_message'] = "Laporan tidak ditemukan.";
    $_SESSION['flash_message_type'] = "error";
    header("Location: laporan_masuk.php");
    exit;
}

// Persiapan untuk Tampilan
$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan';

require_once 'templates/header.php';
?>

<?php 
if(isset($_SESSION['flash_message'])) {
    echo '<div class="mb-4 p-4 rounded-md ' . ($_SESSION['flash_message_type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') . '">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Detail Laporan & Penilaian</h2>
        <a href="laporan_masuk.php" class="text-sm text-blue-500 hover:underline">&larr; Kembali ke Daftar Laporan</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold border-b pb-2 mb-4">Informasi Laporan</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-semibold text-gray-500">Praktikum</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($laporan['nama_prak']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Modul</p>
                    <p class="text-gray-800">Modul <?php echo htmlspecialchars($laporan['nomor_modul']); ?>: <?php echo htmlspecialchars($laporan['nama_modul']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Mahasiswa</p>
                    <p class="text-gray-800"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
                    <p class="text-gray-500"><?php echo htmlspecialchars($laporan['email_mahasiswa']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-gray-500">Tanggal Kumpul</p>
                    <p class="text-gray-800"><?php echo date('d F Y, H:i', strtotime($laporan['tanggal_upload'])); ?></p>
                </div>
                <div>
                    <a href="<?php echo htmlspecialchars($laporan['file_lapor_path']); ?>" download class="mt-4 inline-block w-full text-center bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Unduh Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold border-b pb-2 mb-4">Form Penilaian</h3>
            <form action="beri_nilai.php?laporan_id=<?php echo $laporan_id; ?>" method="POST">
                <div class="mb-4">
                    <label for="nilai" class="block text-gray-700 font-semibold">Nilai (0-100)</label>
                    <input type="number" step="0.01" min="0" max="100" name="nilai" id="nilai" class="w-full mt-1 p-2 border rounded-md" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" required>
                </div>
                <div class="mb-6">
                    <label for="feedback_asisten" class="block text-gray-700 font-semibold">Feedback / Komentar</slabel>
                    <textarea name="feedback_asisten" id="feedback_asisten" rows="8" class="w-full mt-1 p-2 border rounded-md"><?php echo htmlspecialchars($laporan['feedback_asisten'] ?? ''); ?></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="simpan_nilai" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                        Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>