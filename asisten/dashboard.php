<?php
// asisten/dashboard.php

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

require_once 'templates/header.php';
require_once '../includes/config.php';

// --- FUNGSI BANTU ---

// Fungsi untuk mengubah timestamp menjadi format "time ago" (contoh: 10 menit lalu)
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $strTime = array("detik", "menit", "jam", "hari", "bulan", "tahun");
    $length = array("60", "60", "24", "30", "12", "10");

    $currentTime = time();
    if ($currentTime >= $timestamp) {
        $diff = $currentTime - $timestamp;
        for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
            $diff = $diff / $length[$i];
        }

        $diff = round($diff);
        return $diff . " " . $strTime[$i] . " yang lalu";
    }
}

// Fungsi untuk mendapatkan inisial dari nama
function get_initials($name) {
    $words = explode(" ", $name);
    $initials = "";
    $i = 0;
    foreach ($words as $w) {
        if ($i < 2) {
            $initials .= mb_substr($w, 0, 1);
            $i++;
        }
    }
    return strtoupper($initials);
}

// --- LOGIKA PENGAMBILAN DATA STATISTIK ---
$namaAsisten = $_SESSION['nama'] ?? 'Asisten';

// 1. Total Modul Diajarkan
$total_modul_result = $conn->query("SELECT COUNT(id) as total FROM modul_prak");
$total_modul = $total_modul_result->fetch_assoc()['total'];

// 2. Total Laporan Masuk (semua laporan)
$total_laporan_result = $conn->query("SELECT COUNT(id) as total FROM laporan_prak");
$total_laporan = $total_laporan_result->fetch_assoc()['total'];

// 3. Laporan Belum Dinilai
$laporan_belum_dinilai_result = $conn->query("SELECT COUNT(id) as total FROM laporan_prak WHERE status_laporan = 'belum dinilai'");
$laporan_belum_dinilai = $laporan_belum_dinilai_result->fetch_assoc()['total'];


// 4. Ambil 5 laporan terakhir yang masuk untuk aktivitas terbaru
$sql_recent = "SELECT 
                    lp.tanggal_upload,
                    u.nama AS nama_mahasiswa,
                    mp.nama_modul
                FROM 
                    laporan_prak AS lp
                JOIN users AS u ON lp.user_id = u.id
                JOIN modul_prak AS mp ON lp.modul_id = mp.id
                ORDER BY lp.tanggal_upload DESC
                LIMIT 5";
$recent_laporan_result = $conn->query($sql_recent);

?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $total_modul; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
           <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $total_laporan; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
           <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold text-gray-900 mb-5">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-5">
        <?php if ($recent_laporan_result->num_rows > 0): ?>
            <?php while($laporan = $recent_laporan_result->fetch_assoc()): ?>
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 font-bold flex items-center justify-center">
                            <?php echo get_initials($laporan['nama_mahasiswa']); ?>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800">
                            <span class="font-bold"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></span>
                            mengumpulkan laporan untuk <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($laporan['nama_modul']); ?></span>.
                        </p>
                        <p class="text-xs text-gray-500">
                            <?php echo time_ago($laporan['tanggal_upload']); ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-gray-500 py-4">Belum ada aktivitas laporan masuk.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>