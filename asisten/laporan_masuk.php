<?php
// admin/laporan_masuk.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Keamanan dasar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/config.php';

// --- BAGIAN LOGIKA & PENGAMBILAN DATA ---

// Ambil data untuk mengisi dropdown filter
$praktikum_options = $conn->query("SELECT id, nama_prak FROM mata_prak ORDER BY nama_prak ASC");
$mahasiswa_options = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa' ORDER BY nama ASC");

// Ambil nilai filter dari URL (jika ada)
$filter_praktikum = $_GET['filter_prak_id'] ?? '';
$filter_mahasiswa = $_GET['filter_user_id'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Bangun query utama dengan JOIN beberapa tabel
$sql = "SELECT 
            lp.id, lp.tanggal_upload, lp.status_laporan, lp.nilai,
            u.nama AS nama_mahasiswa,
            mp.nama_modul, mp.nomor_modul,
            prak.nama_prak
        FROM 
            laporan_prak AS lp
        JOIN 
            users AS u ON lp.user_id = u.id
        JOIN 
            modul_prak AS mp ON lp.modul_id = mp.id
        JOIN 
            mata_prak AS prak ON mp.mata_prak_id = prak.id";

// Bangun klausa WHERE secara dinamis berdasarkan filter
$where_clauses = [];
$params = [];
$types = '';

if (!empty($filter_praktikum)) {
    $where_clauses[] = "prak.id = ?";
    $params[] = $filter_praktikum;
    $types .= 'i';
}
if (!empty($filter_mahasiswa)) {
    $where_clauses[] = "u.id = ?";
    $params[] = $filter_mahasiswa;
    $types .= 'i';
}
if (!empty($filter_status)) {
    $where_clauses[] = "lp.status_laporan = ?";
    $params[] = $filter_status;
    $types .= 's';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY lp.tanggal_upload DESC";

// Eksekusi query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_laporan = $stmt->get_result();

// Persiapan untuk Tampilan
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan'; // Sesuaikan dengan nama di header_asisten.php

require_once 'templates/header.php';
?>

<div class="bg-white p-6 rounded-lg shadow-lg mb-6">
    <h2 class="text-2xl font-bold mb-4">Filter Laporan</h2>
    <form action="laporan_masuk.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label for="filter_prak_id" class="block text-sm font-medium text-gray-700">Praktikum</label>
            <select name="filter_prak_id" id="filter_prak_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                <option value="">Semua Praktikum</option>
                <?php while($prak = $praktikum_options->fetch_assoc()): ?>
                    <option value="<?php echo $prak['id']; ?>" <?php echo ($filter_praktikum == $prak['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prak['nama_prak']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_user_id" class="block text-sm font-medium text-gray-700">Mahasiswa</label>
            <select name="filter_user_id" id="filter_user_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                <option value="">Semua Mahasiswa</option>
                 <?php while($mhs = $mahasiswa_options->fetch_assoc()): ?>
                    <option value="<?php echo $mhs['id']; ?>" <?php echo ($filter_mahasiswa == $mhs['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mhs['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="filter_status" id="filter_status" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                <option value="">Semua Status</option>
                <option value="belum dinilai" <?php echo ($filter_status == 'belum dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
                <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
            </select>
        </div>
        <div class="flex gap-x-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">Filter</button>
            <a href="laporan_masuk.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded w-full text-center">Reset</a>
        </div>
    </form>
</div>


<div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Daftar Laporan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
    <thead class="bg-gray-800 text-white">
        <tr>
            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Praktikum</th>
            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Modul</th>
            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Mahasiswa</th>
            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tanggal Kumpul</th>
            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Status</th>
            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Nilai</th>
            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
        </tr>
    </thead>
    <tbody class="text-gray-700">
        <?php if ($result_laporan->num_rows === 0): ?>
            <tr><td colspan="7" class="p-4 text-center text-gray-500">Tidak ada laporan yang cocok dengan filter.</td></tr>
        <?php else: ?>
            <?php while($laporan = $result_laporan->fetch_assoc()): ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_prak']); ?></td>
                <td class="py-3 px-4">Modul <?php echo htmlspecialchars($laporan['nomor_modul']); ?></td>
                <td class="py-3 px-4"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></td>
                <td class="py-3 px-4"><?php echo date('d M Y, H:i', strtotime($laporan['tanggal_upload'])); ?></td>
                <td class="py-3 px-4 text-center">
                    <?php if ($laporan['status_laporan'] == 'dinilai'): ?>
                        <span class="bg-green-200 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Dinilai</span>
                    <?php else: ?>
                        <span class="bg-yellow-200 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Belum Dinilai</span>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-4 text-center font-bold"><?php echo $laporan['nilai'] ?? '-'; ?></td>
                <td class="py-3 px-4 text-center">
                    <a href="beri_nilai.php?laporan_id=<?php echo $laporan['id']; ?>" class="bg-indigo-500 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-3 rounded whitespace-nowrap">
                        Detail & Nilai
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>