<?php
// mahasiswa/dashboard.php

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

require_once 'templates/header_mahasiswa.php';
require_once '../includes/config.php';

$loggedInUserId = $_SESSION['user_id'];
$namaMahasiswa = $_SESSION['nama'];

// --- BLOK PHP UNTUK MENGAMBIL DATA (TETAP SAMA SEPERTI SEBELUMNYA) ---

// 1. Hitung jumlah praktikum yang diikuti
$stmt_prak = $conn->prepare("SELECT COUNT(id) as total FROM daftar_prak WHERE user_id = ?");
$stmt_prak->bind_param("i", $loggedInUserId);
$stmt_prak->execute();
$total_praktikum = $stmt_prak->get_result()->fetch_assoc()['total'];
$stmt_prak->close();

// 2. Hitung jumlah tugas/laporan yang sudah dikumpulkan dan dinilai
$stmt_selesai = $conn->prepare("SELECT COUNT(id) as total FROM laporan_prak WHERE user_id = ? AND status_laporan = 'dinilai'");
$stmt_selesai->bind_param("i", $loggedInUserId);
$stmt_selesai->execute();
$tugas_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'];
$stmt_selesai->close();

// 3. Hitung jumlah tugas yang masih menunggu
$stmt_total_modul = $conn->prepare("
    SELECT COUNT(m.id) as total 
    FROM modul_prak m 
    JOIN daftar_prak dp ON m.mata_prak_id = dp.mata_prak_id 
    WHERE dp.user_id = ?");
$stmt_total_modul->bind_param("i", $loggedInUserId);
$stmt_total_modul->execute();
$total_modul = $stmt_total_modul->get_result()->fetch_assoc()['total'];
$stmt_total_modul->close();

$stmt_dikumpul = $conn->prepare("SELECT COUNT(id) as total FROM laporan_prak WHERE user_id = ?");
$stmt_dikumpul->bind_param("i", $loggedInUserId);
$stmt_dikumpul->execute();
$tugas_dikumpul = $stmt_dikumpul->get_result()->fetch_assoc()['total'];
$stmt_dikumpul->close();

$tugas_menunggu = $total_modul - $tugas_dikumpul;

// 4. Ambil notifikasi terbaru
$notifikasi = [];
// a. Ambil nilai yang baru masuk
$sql_nilai = "SELECT lp.tanggal_dinilai as tgl_event, mp.nama_modul, 'nilai' as tipe 
              FROM laporan_prak lp 
              JOIN modul_prak mp ON lp.modul_id = mp.id
              WHERE lp.user_id = ? AND lp.status_laporan = 'dinilai' 
              ORDER BY lp.tanggal_dinilai DESC LIMIT 2";
$stmt_nilai = $conn->prepare($sql_nilai);
$stmt_nilai->bind_param("i", $loggedInUserId);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while($row = $result_nilai->fetch_assoc()){ $notifikasi[] = $row; }

// b. Ambil deadline yang akan datang
$sql_deadline = "SELECT m.due_date as tgl_event, m.nama_modul, 'deadline' as tipe
                 FROM modul_prak m
                 JOIN daftar_prak dp ON m.mata_prak_id = dp.mata_prak_id
                 WHERE dp.user_id = ? 
                 AND m.due_date > NOW() 
                 AND m.id NOT IN (SELECT modul_id FROM laporan_prak WHERE user_id = ?)
                 ORDER BY m.due_date ASC LIMIT 2";
$stmt_deadline = $conn->prepare($sql_deadline);
$stmt_deadline->bind_param("ii", $loggedInUserId, $loggedInUserId);
$stmt_deadline->execute();
$result_deadline = $stmt_deadline->get_result();
while($row = $result_deadline->fetch_assoc()){ $notifikasi[] = $row; }

// Urutkan notifikasi berdasarkan tanggal
usort($notifikasi, function($a, $b) {
    return strtotime($b['tgl_event']) - strtotime($a['tgl_event']);
});

?>


<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Praktikum Diikuti</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $total_praktikum; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Tugas Selesai Dinilai</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $tugas_selesai; ?></p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Tugas Menunggu</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo $tugas_menunggu; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-xl font-bold text-gray-900 mb-5">Notifikasi Terbaru</h3>
    <div class="space-y-5">
        <?php if (empty($notifikasi)): ?>
            <p class="text-center text-gray-500 py-4">Tidak ada notifikasi baru untuk Anda.</p>
        <?php else: ?>
            <?php foreach (array_slice($notifikasi, 0, 4) as $notif): ?>
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 mt-1">
                        <?php if ($notif['tipe'] == 'nilai'): ?>
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        <?php else: // tipe 'deadline' ?>
                             <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800">
                        <?php if ($notif['tipe'] == 'nilai'): ?>
                            Nilai untuk <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($notif['nama_modul']); ?></span> telah diberikan. Cek di halaman praktikum.
                        <?php else: ?>
                            Batas pengumpulan untuk <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($notif['nama_modul']); ?></span> akan berakhir pada <span class="font-semibold"><?php echo date('d F Y', strtotime($notif['tgl_event'])); ?></span>.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Panggil footer
require_once 'templates/footer_mahasiswa.php';
?>