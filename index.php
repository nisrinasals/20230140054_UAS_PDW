<?php
//index.php
session_start();
require_once 'includes/config.php';

// Logika sederhana untuk menentukan apakah user sudah login dan perannya
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : 'guest';

// Jika sudah login, arahkan ke dashboard masing-masing
if ($isLoggedIn) {
    if ($userRole == 'asisten') {
        header("Location: asisten/dashboard.php");
        exit();
    } elseif ($userRole == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di SIMPRAK</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .card-custom {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            /* Tailwind shadow-lg */
        }
    </style>
</head>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <nav class="bg-gray-800 p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white">SIMPRAK</a>
            <div class="space-x-4">
                <a href="login.php"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">Login</a>
                <a href="register.php"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md transition duration-300">Registrasi</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-4">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Selamat Datang di SIMPRAK</h1>
        <p class="text-lg text-center text-gray-600 mb-12">Sistem Informasi Praktikum untuk mempermudah manajemen dan
            pelaksanaan praktikum.</p>

        <div class="space-y-6 mb-12 max-w-2xl mx-auto">
            <div class="bg-white p-6 rounded-lg shadow-lg card-custom w-full">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Mencari Mata Praktikum</h2>
                <p class="text-gray-600 mb-4">Temukan semua mata praktikum yang tersedia di sistem.</p>
                <a href="mahasiswa/cari_praktikum.php" class="text-blue-500 hover:text-blue-700 font-medium">Cari
                    Praktikum &rarr;</a>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg card-custom w-full">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Melihat Praktikum Diikuti</h2>
                <p class="text-gray-600 mb-4">Lihat daftar praktikum yang sedang atau pernah Anda ikuti.</p>
                <a href="mahasiswa/my_praktikum.php" class="text-blue-500 hover:text-blue-700 font-medium">Lihat
                    Praktikumku &rarr;</a>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg card-custom w-full">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Melihat Laporan Masuk</h2>
                <p class="text-gray-600 mb-4">Cek status dan nilai laporan praktikum yang telah Anda kirim.</p>
                <a href="asisten/laporan_masuk.php" class="text-blue-500 hover:text-blue-700 font-medium">Cek Laporan
                    &rarr;</a>
            </div>
        </div>

    </div>

    <footer class="bg-gray-800 text-white text-center p-6 mt-12">
        <p>&copy; <?php echo date("Y"); ?> SIMPRAK. All rights reserved.</p>
    </footer>

</body>

</html>