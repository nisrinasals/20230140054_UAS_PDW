<?php
// admin/kelola_modul.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Keamanan dasar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

// Ambil ID praktikum dari URL. Ini adalah kunci halaman ini.
$prak_id = isset($_GET['prak_id']) && is_numeric($_GET['prak_id']) ? intval($_GET['prak_id']) : 0;
if ($prak_id === 0) {
    header("Location: kelola_prak.php"); // Jika tidak ada ID, kembali ke daftar praktikum
    exit;
}

// --- LOGIKA UNTUK PROSES FORM (TAMBAH/EDIT MODUL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_modul = $_POST['nomor_modul'];
    $nama_modul = $_POST['nama_modul'];
    $desc_modul = $_POST['desc_modul'];
    $due_date = $_POST['due_date'];

    $file_path = ''; // Path file default kosong

    // Logika Upload File
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
        $target_dir = "../uploads/materi/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $original_name = basename($_FILES["file_materi"]["name"]);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        // Buat nama file unik untuk menghindari tumpang tindih
        $unique_name = "materi_" . $prak_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_name;

        // Pindahkan file yang di-upload ke direktori tujuan
        if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            // Handle error upload jika perlu
            $_SESSION['flash_message'] = "Gagal mengunggah file materi.";
            $_SESSION['flash_message_type'] = "error";
            header("Location: kelola_modul.php?prak_id=$prak_id");
            exit;
        }
    }

    // Aksi: Tambah Modul Baru
    if (isset($_POST['tambah_modul'])) {
        $stmt = $conn->prepare("INSERT INTO modul_prak (mata_prak_id, nomor_modul, nama_modul, desc_modul, file_modul_path, due_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $prak_id, $nomor_modul, $nama_modul, $desc_modul, $file_path, $due_date);
        $stmt->execute();
        $_SESSION['flash_message'] = "Modul baru berhasil ditambahkan.";
    }

    // Aksi: Edit Modul
    if (isset($_POST['edit_modul'])) {
        $modul_id = intval($_POST['modul_id']);
        // Jika tidak ada file baru yang diupload, jangan ubah path file lama
        if (empty($file_path)) {
            $stmt = $conn->prepare("UPDATE modul_prak SET nomor_modul=?, nama_modul=?, desc_modul=?, due_date=? WHERE id=?");
            $stmt->bind_param("isssi", $nomor_modul, $nama_modul, $desc_modul, $due_date, $modul_id);
        } else {
            // Jika ada file baru, update pathnya juga (opsional: hapus file lama)
            $stmt = $conn->prepare("UPDATE modul_prak SET nomor_modul=?, nama_modul=?, desc_modul=?, file_modul_path=?, due_date=? WHERE id=?");
            $stmt->bind_param("issssi", $nomor_modul, $nama_modul, $desc_modul, $file_path, $due_date, $modul_id);
        }
        $stmt->execute();
        $_SESSION['flash_message'] = "Modul berhasil diperbarui.";
    }

    $_SESSION['flash_message_type'] = "success";
    header("Location: kelola_modul.php?prak_id=$prak_id");
    exit;
}

// --- LOGIKA UNTUK AKSI DELETE ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['modul_id'])) {
    $modul_id_to_delete = intval($_GET['modul_id']);

    // Opsional: Hapus file fisik dari server sebelum hapus record DB
    $stmt_getfile = $conn->prepare("SELECT file_modul_path FROM modul_prak WHERE id = ?");
    $stmt_getfile->bind_param("i", $modul_id_to_delete);
    $stmt_getfile->execute();
    $result_file = $stmt_getfile->get_result()->fetch_assoc();
    if ($result_file && !empty($result_file['file_modul_path']) && file_exists($result_file['file_modul_path'])) {
        unlink($result_file['file_modul_path']);
    }
    $stmt_getfile->close();

    // Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM modul_prak WHERE id = ?");
    $stmt_delete->bind_param("i", $modul_id_to_delete);
    $stmt_delete->execute();

    $_SESSION['flash_message'] = "Modul berhasil dihapus.";
    $_SESSION['flash_message_type'] = "success";
    header("Location: kelola_modul.php?prak_id=$prak_id");
    exit;
}


// --- PENGAMBILAN DATA UNTUK TAMPILAN ---
// Ambil info praktikum untuk judul halaman
$stmt_prak = $conn->prepare("SELECT nama_prak FROM mata_prak WHERE id = ?");
$stmt_prak->bind_param("i", $prak_id);
$stmt_prak->execute();
$praktikum = $stmt_prak->get_result()->fetch_assoc();
if (!$praktikum) {
    header("Location: kelola_prak.php");
    exit;
} // jika ID prak tidak valid

// Ambil semua modul untuk praktikum ini
$modul_list = [];
$stmt_modul = $conn->prepare("SELECT * FROM modul_prak WHERE mata_prak_id = ? ORDER BY nomor_modul ASC");
$stmt_modul->bind_param("i", $prak_id);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
while ($row = $result_modul->fetch_assoc()) {
    $modul_list[] = $row;
}

// Persiapan untuk Tampilan
$pageTitle = 'Kelola Modul: ' . htmlspecialchars($praktikum['nama_prak']);
$activePage = 'kelola_modul';

// Logika untuk form edit
$action = $_GET['action'] ?? 'list';
$modul_to_edit = null;
if ($action === 'edit' && isset($_GET['modul_id'])) {
    $stmt = $conn->prepare("SELECT * FROM modul_prak WHERE id = ?");
    $stmt->bind_param("i", $_GET['modul_id']);
    $stmt->execute();
    $modul_to_edit = $stmt->get_result()->fetch_assoc();
}

require_once 'templates/header.php';
// ... (Kode HTML lengkap ada di bawah) ...
?>

<?php
if (isset($_SESSION['flash_message'])) {
    echo '<div class="mb-4 p-4 rounded-md ' . ($_SESSION['flash_message_type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') . '">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h2>
        <a href="kelola_prak.php" class="text-sm text-blue-500 hover:underline">&larr; Kembali ke Daftar Praktikum</a>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="kelola_modul.php?prak_id=<?php echo $prak_id; ?>&action=tambah"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Tambah Modul
        </a>
    <?php endif; ?>
</div>


<?php if ($action === 'tambah' || $action === 'edit'): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl mx-auto">
        <h3 class="text-xl font-bold mb-6"><?php echo $action === 'edit' ? 'Edit Modul' : 'Tambah Modul Baru'; ?></h3>
        <form action="kelola_modul.php?prak_id=<?php echo $prak_id; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="edit_modul" value="1">
                <input type="hidden" name="modul_id" value="<?php echo $modul_to_edit['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="tambah_modul" value="1">
            <?php endif; ?>

            <div class="mb-4">
                <label for="nomor_modul" class="block text-gray-700">Nomor Modul</label>
                <input type="number" name="nomor_modul" id="nomor_modul" class="w-full mt-1 p-2 border rounded"
                    value="<?php echo htmlspecialchars($modul_to_edit['nomor_modul'] ?? ''); ?>" required>
            </div>
            <div class="mb-4">
                <label for="nama_modul" class="block text-gray-700">Nama Modul</label>
                <input type="text" name="nama_modul" id="nama_modul" class="w-full mt-1 p-2 border rounded"
                    value="<?php echo htmlspecialchars($modul_to_edit['nama_modul'] ?? ''); ?>" required>
            </div>
            <div class="mb-4">
                <label for="desc_modul" class="block text-gray-700">Deskripsi</label>
                <textarea name="desc_modul" id="desc_modul" rows="4"
                    class="w-full mt-1 p-2 border rounded"><?php echo htmlspecialchars($modul_to_edit['desc_modul'] ?? ''); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="due_date" class="block text-gray-700">Batas Waktu Pengumpulan (Due Date)</label>
                <input type="datetime-local" name="due_date" id="due_date" class="w-full mt-1 p-2 border rounded"
                    value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($modul_to_edit['due_date'] ?? 'now'))); ?>">
            </div>
            <div class="mb-4">
                <label for="file_materi" class="block text-gray-700">File Materi (PDF/DOCX/ZIP)</label>
                <input type="file" name="file_materi" id="file_materi" class="w-full mt-1 p-2 border rounded">
                <?php if ($action === 'edit' && !empty($modul_to_edit['file_modul_path'])): ?>
                    <p class="text-xs text-gray-500 mt-1">File saat ini:
                        <?php echo basename($modul_to_edit['file_modul_path']); ?>. Kosongkan jika tidak ingin mengubah file.
                    </p>
                <?php endif; ?>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <a href="kelola_modul.php?prak_id=<?php echo $prak_id; ?>"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Batal</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan
                    Modul</button>
            </div>
        </form>
    </div>

<?php else: ?>
    <div class="bg-white p-6 rounded-lg shadow-lg w-full">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">No. Modul</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Modul</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">File Materi</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Due Date</th>
                        <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($modul_list)): ?>
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Belum ada modul untuk praktikum ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($modul_list as $modul): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($modul['nomor_modul']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($modul['nama_modul']); ?></td>
                                <td class="py-3 px-4">
                                    <?php if (!empty($modul['file_modul_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($modul['file_modul_path']); ?>"
                                            class="text-blue-500 hover:underline"
                                            download><?php echo basename($modul['file_modul_path']); ?></a>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php echo !empty($modul['due_date']) ? date('d M Y, H:i', strtotime($modul['due_date'])) : '-'; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-center gap-x-2">
                                        <a href="kelola_modul.php?prak_id=<?php echo $prak_id; ?>&action=edit&modul_id=<?php echo $modul['id']; ?>"
                                            class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded">Edit</a>
                                        <a href="kelola_modul.php?prak_id=<?php echo $prak_id; ?>&action=delete&modul_id=<?php echo $modul['id']; ?>"
                                            class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-2 px-3 rounded"
                                            onclick="return confirm('Anda yakin ingin menghapus modul ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>