<?php
// mahasiswa/my_praktikum.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

$loggedInUserId = $_SESSION['user_id'];
$pageTitle = 'Praktikum Saya';
$activePage = 'my_praktikum';

// --- Logika untuk mengambil data praktikum yang diikuti (SUDAH DISESUAIKAN) ---
$praktikum_terdaftar = [];

// Menggunakan tabel `mata_prak` dan `daftar_prak` sesuai struktur DB Anda
$sql = "SELECT 
            mp.id, 
            mp.nama_prak, 
            mp.kode_prak, 
            mp.deskripsi
        FROM 
            mata_prak AS mp
        JOIN 
            daftar_prak AS dp ON mp.id = dp.mata_prak_id
        WHERE 
            dp.user_id = ? AND mp.is_active = TRUE
        ORDER BY
            mp.nama_prak ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $praktikum_terdaftar[] = $row;
}

$stmt->close();
$conn->close();

require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-8 rounded-lg shadow-lg w-full">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Praktikum Saya</h2>

    <?php if (empty($praktikum_terdaftar)): ?>
        <div class="text-center border-2 border-dashed border-gray-300 rounded-lg p-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum Ada Praktikum</h3>
            <p class="mt-1 text-sm text-gray-500">Anda belum terdaftar di praktikum manapun.</p>
            <div class="mt-6">
                <a href="cari_praktikum.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cari Praktikum Sekarang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($praktikum_terdaftar as $praktikum): ?>
                <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($praktikum['nama_prak']); ?></h3>
                        
                        <?php if (!empty($praktikum['kode_prak'])): ?>
                            <p class="text-sm text-gray-500 mb-3 font-mono">Kode: <?php echo htmlspecialchars($praktikum['kode_prak']); ?></p>
                        <?php endif; ?>
                        
                        <p class="text-gray-700 text-sm mb-4">
                            <?php
                                $deskripsi_singkat = substr($praktikum['deskripsi'], 0, 100);
                                echo htmlspecialchars($deskripsi_singkat);
                                if (strlen($praktikum['deskripsi']) > 100) {
                                    echo '...';
                                }
                            ?>
                        </p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="detail_praktikum.php?id=<?php echo $praktikum['id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded w-full text-center block transition duration-300 ease-in-out">
                            Lihat Detail & Tugas
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>