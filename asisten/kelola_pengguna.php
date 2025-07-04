<?php
// admin/kelola_pengguna.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

// --- LOGIKA PROSES FORM (CREATE & UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi: Tambah Pengguna Baru
    if (isset($_POST['tambah_pengguna'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        if (empty($nama) || empty($email) || empty($password) || empty($role)) {
            $_SESSION['flash_message'] = "Semua field harus diisi.";
            $_SESSION['flash_message_type'] = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = "Format email tidak valid.";
            $_SESSION['flash_message_type'] = "error";
        } else {
            // Cek apakah email sudah ada
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $_SESSION['flash_message'] = "Email sudah terdaftar. Gunakan email lain.";
                $_SESSION['flash_message_type'] = "error";
            } else {
                // Hash password sebelum disimpan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);
                if ($stmt_insert->execute()) {
                    $_SESSION['flash_message'] = "Pengguna baru berhasil ditambahkan.";
                    $_SESSION['flash_message_type'] = "success";
                }
            }
        }
    }

    // Aksi: Edit Pengguna
    if (isset($_POST['edit_pengguna'])) {
        $id = intval($_POST['id']);
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $password = $_POST['password'];

        // Cek apakah password diisi (artinya ingin diubah)
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_update = $conn->prepare("UPDATE users SET nama=?, email=?, role=?, password=? WHERE id=?");
            $stmt_update->bind_param("ssssi", $nama, $email, $role, $hashed_password, $id);
        } else {
            // Jika password kosong, jangan update password
            $stmt_update = $conn->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
            $stmt_update->bind_param("sssi", $nama, $email, $role, $id);
        }
        
        if ($stmt_update->execute()) {
            $_SESSION['flash_message'] = "Data pengguna berhasil diperbarui.";
            $_SESSION['flash_message_type'] = "success";
        }
    }
    
    header("Location: kelola_pengguna.php");
    exit;
}

// --- LOGIKA AKSI DELETE ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    // Keamanan: Mencegah admin menghapus akunnya sendiri
    if ($id_to_delete == $_SESSION['user_id']) {
        $_SESSION['flash_message'] = "Anda tidak bisa menghapus akun Anda sendiri.";
        $_SESSION['flash_message_type'] = "error";
    } else {
        $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->bind_param("i", $id_to_delete);
        if ($stmt_delete->execute()) {
            $_SESSION['flash_message'] = "Pengguna berhasil dihapus.";
            $_SESSION['flash_message_type'] = "success";
        }
    }
    header("Location: kelola_pengguna.php");
    exit;
}

// --- PENGAMBILAN DATA UNTUK TAMPILAN ---
$action = $_GET['action'] ?? 'list';
$pageTitle = 'Kelola Akun Pengguna';
$activePage = 'kelola_pengguna';

$user_to_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $user_to_edit = $stmt->get_result()->fetch_assoc();
}

// Ambil semua pengguna kecuali passwordnya
$user_list = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY id ASC");

require_once 'templates/header.php';
?>

<?php 
if(isset($_SESSION['flash_message'])) {
    echo '<div class="mb-4 p-4 rounded-md ' . ($_SESSION['flash_message_type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') . '">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
}
?>

<?php if ($action === 'tambah' || $action === 'edit'): ?>
<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg mx-auto">
    <h2 class="text-2xl font-bold mb-6"><?php echo $action === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h2>
    <form action="kelola_pengguna.php" method="POST">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="edit_pengguna" value="1">
            <input type="hidden" name="id" value="<?php echo $user_to_edit['id']; ?>">
        <?php else: ?>
            <input type="hidden" name="tambah_pengguna" value="1">
        <?php endif; ?>

        <div class="mb-4">
            <label for="nama" class="block text-gray-700">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" class="w-full mt-1 p-2 border rounded" value="<?php echo htmlspecialchars($user_to_edit['nama'] ?? ''); ?>" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700">Email</label>
            <input type="email" name="email" id="email" class="w-full mt-1 p-2 border rounded" value="<?php echo htmlspecialchars($user_to_edit['email'] ?? ''); ?>" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password" name="password" id="password" class="w-full mt-1 p-2 border rounded" <?php echo ($action === 'tambah') ? 'required' : ''; ?>>
            <?php if ($action === 'edit'): ?>
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
            <?php endif; ?>
        </div>
        <div class="mb-6">
            <label for="role" class="block text-gray-700">Peran (Role)</label>
            <select name="role" id="role" class="w-full mt-1 p-2 border rounded" required>
                <option value="mahasiswa" <?php echo (isset($user_to_edit) && $user_to_edit['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                <option value="asisten" <?php echo (isset($user_to_edit) && $user_to_edit['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
            </select>
        </div>
        <div class="flex justify-end gap-4">
            <a href="kelola_pengguna.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Batal</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
        </div>
    </form>
</div>

<?php else: ?>
<div class="bg-white p-8 rounded-lg shadow-lg w-full">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Pengguna</h2>
        <a href="kelola_pengguna.php?action=tambah" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Tambah Pengguna
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                    <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nama</th>
                    <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                    <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Role</th>
                    <th class="w-2/12 text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php while($user = $user_list->fetch_assoc()): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-4"><?php echo $user['id']; ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs <?php echo $user['role'] == 'asisten' ? 'bg-green-200 text-green-800' : 'bg-blue-200 text-blue-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-x-2">
                                <a href="kelola_pengguna.php?action=edit&id=<?php echo $user['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded">
                                    Edit
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="kelola_pengguna.php?action=delete&id=<?php echo $user['id']; ?>" class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-2 px-3 rounded" onclick="return confirm('PERINGATAN: Menghapus pengguna akan menghapus semua data terkait. Anda yakin?')">
                                        Hapus
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>