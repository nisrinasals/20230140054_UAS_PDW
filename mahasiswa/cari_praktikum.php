<?php
// mahasiswa/cari_praktikum.php

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi database
require_once '../includes/config.php'; 

// Variabel untuk header dan sidebar aktif
$pageTitle = 'Cari Praktikum';
$activePage = 'cari_praktikum';

// Logika untuk mengecek status login dan peran
$isLoggedIn = isset($_SESSION['user_id']);
$loggedInUserId = $isLoggedIn ? $_SESSION['user_id'] : null;
$userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';

$message = '';
$messageType = ''; // 'success' atau 'error'

// --- [REVISI] Logika Pendaftaran Praktikum ---
if ($isLoggedIn && $userRole == 'mahasiswa' && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['praktikum_id'])) {
        $praktikumIdToRegister = intval($_POST['praktikum_id']);

        // 1. Cek apakah praktikum_id valid di tabel `mata_prak`
        $stmt_check_praktikum = $conn->prepare("SELECT id FROM mata_prak WHERE id = ? AND is_active = TRUE");
        $stmt_check_praktikum->bind_param("i", $praktikumIdToRegister);
        $stmt_check_praktikum->execute();
        $stmt_check_praktikum->store_result();

        if ($stmt_check_praktikum->num_rows == 0) {
            $message = "Praktikum tidak ditemukan atau tidak aktif.";
            $messageType = 'error';
        } else {
            // 2. Cek apakah mahasiswa sudah terdaftar di tabel `daftar_prak`
            $stmt_check_daftar = $conn->prepare("SELECT id FROM daftar_prak WHERE user_id = ? AND mata_prak_id = ?");
            $stmt_check_daftar->bind_param("ii", $loggedInUserId, $praktikumIdToRegister);
            $stmt_check_daftar->execute();
            $stmt_check_daftar->store_result();

            if ($stmt_check_daftar->num_rows > 0) {
                $message = "Anda sudah terdaftar di praktikum ini.";
                $messageType = 'error';
            } else {
                // 3. Daftarkan mahasiswa ke tabel `daftar_prak`
                $stmt_daftar = $conn->prepare("INSERT INTO daftar_prak (user_id, mata_prak_id) VALUES (?, ?)");
                $stmt_daftar->bind_param("ii", $loggedInUserId, $praktikumIdToRegister);

                if ($stmt_daftar->execute()) {
                    $message = "Berhasil mendaftar ke praktikum!";
                    $messageType = 'success';
                } else {
                    $message = "Gagal mendaftar ke praktikum. Silakan coba lagi. " . $stmt_daftar->error;
                    $messageType = 'error';
                }
                $stmt_daftar->close();
            }
            $stmt_check_daftar->close();
        }
        $stmt_check_praktikum->close();
    }
}

// --- [REVISI] Logika Menampilkan Mata Praktikum ---
$search_query = $_GET['q'] ?? '';
$praktikum_list = [];

// Mengambil dari tabel `mata_prak`
$sql_praktikum = "SELECT id, nama_prak, kode_prak, deskripsi FROM mata_prak WHERE is_active = TRUE";
$params = [];
$types = '';

if (!empty($search_query)) {
    // Mencari berdasarkan `nama_prak`
    $sql_praktikum .= " AND (nama_prak LIKE ? OR deskripsi LIKE ? OR kode_prak LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$stmt_praktikum = $conn->prepare($sql_praktikum);
if (!empty($params)) {
    $stmt_praktikum->bind_param($types, ...$params);
}
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result();

while ($row = $result_praktikum->fetch_assoc()) {
    $praktikum_list[] = $row;
}
$stmt_praktikum->close();

// Sertakan header
require_once 'templates/header_mahasiswa.php';
?>

<div class="bg-white p-8 rounded-lg shadow-lg mb-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Cari Mata Praktikum</h2>

    <?php if (!empty($message)): ?>
        <div class="p-3 mb-4 rounded-md text-sm
            <?php echo ($messageType == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="cari_praktikum.php" method="GET" class="mb-6 flex gap-4">
        <input type="text" name="q" placeholder="Cari nama, kode, atau deskripsi praktikum..." 
               value="<?php echo htmlspecialchars($search_query); ?>"
               class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cari</button>
        <?php if (!empty($search_query)): ?>
            <a href="cari_praktikum.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Reset</a>
        <?php endif; ?>
    </form>

    <?php if (empty($praktikum_list)): ?>
        <p class="text-gray-600 text-center py-10">Tidak ada mata praktikum yang ditemukan.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($praktikum_list as $praktikum): ?>
                <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($praktikum['nama_prak']); ?></h3>
                    <?php if (!empty($praktikum['kode_prak'])): ?>
                        <p class="text-sm text-gray-500 mb-3">Kode: <?php echo htmlspecialchars($praktikum['kode_prak']); ?></p>
                    <?php endif; ?>
                    <p class="text-gray-700 text-sm mb-4"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>
                    
                    <?php if ($isLoggedIn && $userRole == 'mahasiswa'): ?>
                        <?php
                            // [REVISI] Cek status pendaftaran di tabel `daftar_prak`
                            $isRegistered = false;
                            $stmt_check_status = $conn->prepare("SELECT COUNT(*) FROM daftar_prak WHERE user_id = ? AND mata_prak_id = ?");
                            $stmt_check_status->bind_param("ii", $loggedInUserId, $praktikum['id']);
                            $stmt_check_status->execute();
                            $stmt_check_status->bind_result($count_registered);
                            $stmt_check_status->fetch();
                            $stmt_check_status->close();
                            
                            if ($count_registered > 0) {
                                $isRegistered = true;
                            }
                        ?>
                        <?php if ($isRegistered): ?>
                            <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded cursor-not-allowed w-full" disabled>Sudah Terdaftar</button>
                            <a href="detail_praktikum.php?id=<?php echo $praktikum['id']; ?>" class="mt-2 text-blue-500 hover:underline block text-center text-sm">Lihat Detail Praktikum</a>
                        <?php else: ?>
                            <form action="cari_praktikum.php" method="POST">
                                <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded w-full">Daftar Praktikum</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-600 text-center pt-2">
                            <a href="../login.php" class="text-blue-500 hover:underline">Login</a> untuk mendaftar.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Tutup koneksi sebelum memanggil footer
$conn->close();

// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>