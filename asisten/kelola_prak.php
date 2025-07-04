<?php
// admin/kelola_praktikum.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// KEAMANAN: Pastikan hanya asisten/admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') { // Sesuaikan 'asisten' dengan role Anda
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

// --- BAGIAN LOGIKA UNTUK PROSES DATA (CREATE, UPDATE, TOGGLE STATUS) ---

// Inisialisasi variabel pesan
$message = '';
$messageType = '';

// Proses jika ada form yang di-submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses Tambah Praktikum Baru
    if (isset($_POST['tambah_praktikum'])) {
        $nama_prak = trim($_POST['nama_prak']);
        $kode_prak = trim($_POST['kode_prak']);
        $deskripsi = trim($_POST['deskripsi']);

        if (empty($nama_prak) || empty($kode_prak)) {
            $message = "Nama dan Kode praktikum tidak boleh kosong.";
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO mata_prak (nama_prak, kode_prak, deskripsi) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama_prak, $kode_prak, $deskripsi);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Praktikum baru berhasil ditambahkan!";
                $_SESSION['flash_message_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Gagal menambahkan praktikum: " . $stmt->error;
                $_SESSION['flash_message_type'] = "error";
            }
            $stmt->close();
            header("Location: kelola_prak.php");
            exit;
        }
    }

    // Proses Edit Praktikum
    if (isset($_POST['edit_praktikum'])) {
        $id = intval($_POST['id']);
        $nama_prak = trim($_POST['nama_prak']);
        $kode_prak = trim($_POST['kode_prak']);
        $deskripsi = trim($_POST['deskripsi']);

        if (empty($nama_prak) || empty($id) || empty($kode_prak)) {
            $message = "Nama praktikum, Kode dan, ID tidak boleh kosong.";
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("UPDATE mata_prak SET nama_prak = ?, kode_prak = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama_prak, $kode_prak, $deskripsi, $id);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Data praktikum berhasil diperbarui!";
                $_SESSION['flash_message_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Gagal memperbarui data: " . $stmt->error;
                $_SESSION['flash_message_type'] = "error";
            }
            $stmt->close();
            header("Location: kelola_prak.php");
            exit;
        }
    }
}

// Proses jika ada aksi dari link (GET)
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Ambil status saat ini, lalu balikkan (toggle 0 menjadi 1, 1 menjadi 0)
    $stmt = $conn->prepare("UPDATE mata_prak SET is_active = !is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Status praktikum berhasil diubah.";
        $_SESSION['flash_message_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Gagal mengubah status.";
        $_SESSION['flash_message_type'] = "error";
    }
    $stmt->close();
    header("Location: kelola_prak.php");
    exit;
}

// --- BAGIAN UNTUK MENGAMBIL DATA & MENENTUKAN TAMPILAN ---

// Ambil pesan flash dari session jika ada
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_message_type'];
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}

$action = $_GET['action'] ?? 'list'; // Default adalah 'list'
$pageTitle = 'Kelola Mata Praktikum';
$activePage = 'kelola_prak'; // Untuk sidebar

// Jika action adalah 'edit', ambil data praktikum yang akan diedit
$praktikum_to_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id_to_edit = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM mata_prak WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $praktikum_to_edit = $result->fetch_assoc();
        $pageTitle = 'Edit Praktikum: ' . htmlspecialchars($praktikum_to_edit['nama_prak']);
    } else {
        $message = "Praktikum tidak ditemukan.";
        $messageType = 'error';
        $action = 'list'; // Kembali ke list jika ID tidak valid
    }
    $stmt->close();
}

// Ambil semua data praktikum untuk ditampilkan di tabel (hanya jika action='list')
$praktikum_list = [];
if ($action === 'list') {
    $result = $conn->query("SELECT * FROM mata_prak ORDER BY id ASC");
    while ($row = $result->fetch_assoc()) {
        $praktikum_list[] = $row;
    }
}

// Sertakan header admin
// Pastikan Anda punya file header untuk admin, misal: templates/header_admin.php
require_once 'templates/header.php';

?>

<?php if (!empty($message)): ?>
    <div
        class="mb-4 p-4 rounded-md <?php echo ($messageType == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>


<?php if ($action === 'tambah' || $action === 'edit'): ?>

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <?php echo ($action === 'edit') ? 'Edit Mata Praktikum' : 'Tambah Mata Praktikum Baru'; ?>
        </h2>

        <form action="kelola_prak.php" method="POST">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $praktikum_to_edit['id']; ?>">
                <input type="hidden" name="edit_praktikum" value="1">
            <?php else: ?>
                <input type="hidden" name="tambah_praktikum" value="1">
            <?php endif; ?>

            <div class="mb-4">
                <label for="nama_prak" class="block text-gray-700 text-sm font-bold mb-2">Nama Praktikum</label>
                <input type="text" id="nama_prak" name="nama_prak"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="<?php echo htmlspecialchars($praktikum_to_edit['nama_prak'] ?? ''); ?>" required>
            </div>

            <div class="mb-4">
                <label for="kode_prak" class="block text-gray-700 text-sm font-bold mb-2">Kode Praktikum</label>
                <input type="text" id="kode_prak" name="kode_prak"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="<?php echo htmlspecialchars($praktikum_to_edit['kode_prak'] ?? ''); ?>" required>
            </div>

            <div class="mb-6">
                <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($praktikum_to_edit['deskripsi'] ?? ''); ?></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?php echo ($action === 'edit') ? 'Simpan Perubahan' : 'Tambah Praktikum'; ?>
                </button>
                <a href="kelola_prak.php"
                    class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Batal
                </a>
            </div>
        </form>
    </div>

<?php else: ?>

    <div class="bg-white p-8 rounded-lg shadow-lg w-full">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Daftar Mata Praktikum</h2>
            <a href="kelola_prak.php?action=tambah"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                + Tambah Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Kode</th>
                        <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nama Praktikum</th>
                        <th class="w-2/12 text-center py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="w-3/12 text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($praktikum_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Belum ada data mata praktikum.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($praktikum_list as $prak): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?php echo $prak['id']; ?></td>
                                <td class="py-3 px-4 font-mono"><?php echo htmlspecialchars($prak['kode_prak']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($prak['nama_prak']); ?></td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($prak['is_active']): ?>
                                        <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Aktif</span>
                                    <?php else: ?>
                                        <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center justify-center gap-x-2">
                                        <a href="kelola_prak.php?action=edit&id=<?php echo $prak['id']; ?>"
                                            class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded">
                                            Edit
                                        </a>

                                        <a href="kelola_praktikum.php?action=toggle_status&id=<?php echo $prak['id']; ?>"
                                            class="<?php echo $prak['is_active'] ? 'bg-red-500 hover:bg-red-700' : 'bg-green-500 hover:bg-green-700'; ?> text-white text-xs font-bold py-2 px-3 rounded"
                                            onclick="return confirm('Anda yakin ingin mengubah status praktikum ini?')">
                                            <?php echo $prak['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                        </a>

                                        <a href="kelola_modul.php?prak_id=<?php echo $prak['id']; ?>"
                                            class="bg-gray-500 hover:bg-gray-700 text-white text-xs font-bold py-2 px-3 rounded">
                                            Kelola Modul
                                        </a>
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
// Sertakan footer admin
require_once 'templates/footer.php';
?>