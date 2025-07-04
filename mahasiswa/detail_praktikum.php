<?php
// mahasiswa/detail_praktikum.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Keamanan dasar & inisialisasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/config.php';
$loggedInUserId = $_SESSION['user_id'];
$praktikumId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($praktikumId === 0) {
    header("Location: my_praktikum.php");
    exit;
}

// Verifikasi akses mahasiswa ke praktikum
// ... (logika verifikasi Anda sebelumnya) ...
$stmt_verify = $conn->prepare("SELECT COUNT(*) FROM daftar_prak WHERE user_id = ? AND mata_prak_id = ?");
$stmt_verify->bind_param("ii", $loggedInUserId, $praktikumId);
$stmt_verify->execute();
$stmt_verify->bind_result($count);
$stmt_verify->fetch();
$stmt_verify->close();

if ($count == 0) {
    // Jika hasilnya 0, berarti mahasiswa ini tidak terdaftar di praktikum tersebut.
    // Langsung hentikan proses dan kembalikan dia ke halaman "Praktikum Saya".
    // Anda juga bisa menambahkan pesan error jika mau.
    $_SESSION['error_message'] = "Anda tidak memiliki akses ke praktikum tersebut.";
    header("Location: my_praktikum.php");
    exit;
}
// [DISESUAIKAN] Logika untuk memproses upload laporan ke tabel `laporan_prak`
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_laporan'])) {
    $modulId = intval($_POST['modul_id']);

    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $target_dir = "../uploads/laporan/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_name = basename($_FILES["file_laporan"]["name"]);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_name = "laporan_" . $loggedInUserId . "_" . $modulId . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_name;

        if ($file_extension != "pdf" && $file_extension != "zip" && $file_extension != "rar") {
            $message = "Maaf, hanya file PDF, ZIP, atau RAR yang diizinkan.";
            $messageType = 'error';
        } else {
            if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
                // [DISESUAIKAN] Simpan ke tabel `laporan_prak` dengan kolom `file_lapor_path`
                $stmt_upload = $conn->prepare("INSERT INTO laporan_prak (modul_id, user_id, file_lapor_path) VALUES (?, ?, ?)");
                $stmt_upload->bind_param("iis", $modulId, $loggedInUserId, $target_file);
                if ($stmt_upload->execute()) {
                    $message = "Laporan berhasil diunggah!";
                    $messageType = 'success';
                } else {
                    $message = "Gagal menyimpan data ke database.";
                    $messageType = 'error';
                }
                $stmt_upload->close();
            } else {
                $message = "Terjadi kesalahan saat mengunggah file.";
                $messageType = 'error';
            }
        }
    } else {
        $message = "Silakan pilih file untuk diunggah.";
        $messageType = 'error';
    }
}


// Ambil detail praktikum
$stmt_detail = $conn->prepare("SELECT nama_prak, kode_prak, deskripsi FROM mata_prak WHERE id = ?");
$stmt_detail->bind_param("i", $praktikumId);
$stmt_detail->execute();
$praktikum = $stmt_detail->get_result()->fetch_assoc();
$stmt_detail->close();
if (!$praktikum) {
    header("Location: my_praktikum.php");
    exit;
}

// [DISESUAIKAN] Ambil daftar modul dari tabel `modul_prak`
$modul_list = [];
$stmt_modul = $conn->prepare("SELECT * FROM modul_prak WHERE mata_prak_id = ? ORDER BY nomor_modul ASC");
$stmt_modul->bind_param("i", $praktikumId);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
while ($row = $result_modul->fetch_assoc()) {
    $modul_list[] = $row;
}
$stmt_modul->close();

// [DISESUAIKAN] Ambil data pengumpulan tugas dari tabel `laporan_prak`
$submissions = [];
$modul_ids = array_map(function ($m) {
    return $m['id']; }, $modul_list);
if (!empty($modul_ids)) {
    $placeholders = implode(',', array_fill(0, count($modul_ids), '?'));
    $types = str_repeat('i', count($modul_ids));

    $stmt_submission = $conn->prepare("SELECT * FROM laporan_prak WHERE user_id = ? AND modul_id IN ($placeholders)");
    $stmt_submission->bind_param("i" . $types, $loggedInUserId, ...$modul_ids);
    $stmt_submission->execute();
    $result_submission = $stmt_submission->get_result();
    while ($row = $result_submission->fetch_assoc()) {
        $submissions[$row['modul_id']] = $row;
    }
    $stmt_submission->close();
}


$pageTitle = 'Detail: ' . htmlspecialchars($praktikum['nama_prak']);
$activePage = 'my_praktikum';

require_once 'templates/header_mahasiswa.php';
?>

<?php if (!empty($message)): ?>
    <div
        class="mb-4 p-4 rounded-md <?php echo ($messageType == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>


<div class="bg-white p-8 rounded-lg shadow-lg w-full">

    <div class="border-b border-gray-200 pb-4 mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($praktikum['nama_prak']); ?></h2>
        <p class="text-md text-gray-500 font-mono mt-1">Kode: <?php echo htmlspecialchars($praktikum['kode_prak']); ?>
        </p>
    </div>

    <div>
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Materi & Pengumpulan Laporan</h3>
        <div class="space-y-6">
            <?php if (empty($modul_list)): ?>
                <p class="text-gray-500 italic text-center py-4">Belum ada modul yang ditambahkan untuk praktikum ini.</p>
            <?php else: ?>
                <?php foreach ($modul_list as $modul): ?>
                    <div class="border border-gray-200 rounded-lg p-5">
                        <h4 class="text-lg font-bold text-gray-800">Modul
                            <?php echo htmlspecialchars($modul['nomor_modul']); ?>:
                            <?php echo htmlspecialchars($modul['nama_modul']); ?></h4>
                        <p class="text-sm text-gray-600 mt-1 mb-4"><?php echo nl2br(htmlspecialchars($modul['desc_modul'])); ?>
                        </p>

                        <?php if (!empty($modul['due_date'])): ?>
                            <p class="text-sm text-red-600 font-semibold mb-3">Batas Pengumpulan:
                                <?php echo date('d F Y, H:i', strtotime($modul['due_date'])); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($modul['file_modul_path'])): ?>
                            <a href="<?php echo htmlspecialchars($modul['file_modul_path']); ?>" download
                                class="inline-block bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600 mb-4">
                                Unduh Materi
                            </a>
                        <?php endif; ?>

                        <div class="bg-gray-50 border-t border-gray-200 -m-5 mt-4 p-5">
                            <?php if (isset($submissions[$modul['id']])):
                                $submission = $submissions[$modul['id']];
                                ?>
                                <h5 class="font-semibold text-green-700 mb-2">Laporan Sudah Dikumpulkan</h5>
                                <p class="text-sm text-gray-700">Terkumpul pada:
                                    <?php echo date('d F Y, H:i', strtotime($submission['tanggal_upload'])); ?></p>
                                <p class="text-sm text-gray-700">File: <?php echo basename($submission['file_lapor_path']); ?></p>

                                <div class="mt-3 bg-white p-3 rounded-md border space-y-1">
                                    <h6 class="font-semibold">Penilaian Asisten:</h6>
                                    <?php if ($submission['status_laporan'] == 'dinilai'): ?>

                                        <p class="text-sm">
                                            <b>Status:</b>
                                            <span class="font-medium text-green-700">Sudah Dinilai</span>
                                        </p>

                                        <p class="text-sm">
                                            <b>Nilai:</b>
                                            <span
                                                class="font-bold text-gray-800"><?php echo htmlspecialchars($submission['nilai']); ?></span>
                                        </p>

                                        <?php if (!empty($submission['feedback_asisten'])): ?>
                                            <p class="text-sm pt-2">
                                                <b>Feedback dari Asisten:</b><br>
                                                <span
                                                    class="text-gray-700 italic">"<?php echo nl2br(htmlspecialchars($submission['feedback_asisten'])); ?>"</span>
                                            </p>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <p class="text-sm italic text-gray-500">Laporan Anda sedang dalam proses penilaian.</p>
                                    <?php endif; ?>
                                </div>

                            <?php else: ?>
                                <h5 class="font-semibold text-gray-800 mb-2">Unggah Laporan Anda</h5>
                                <form action="detail_praktikum.php?id=<?php echo $praktikumId; ?>" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                                    <div class="flex items-center space-x-4">
                                        <input type="file" name="file_laporan"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                            required>
                                        <button type="submit" name="submit_laporan"
                                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md whitespace-nowrap">Kumpulkan</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>