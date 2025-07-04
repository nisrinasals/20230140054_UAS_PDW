<?php
// templates/header_asisten.php

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten, lalu arahkan ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php"); // Arahkan ke halaman login utama
    exit();
}

// Menyiapkan variabel untuk judul halaman dan menandai link aktif
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? '';

// Menyiapkan class untuk link aktif dan tidak aktif agar mudah dibaca
$activeClass = 'bg-gray-700 text-white';
$inactiveClass = 'text-gray-400 hover:bg-gray-600 hover:text-white';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-50">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-5 text-center border-b border-gray-700">
            <h3 class="text-xl font-semibold">Panel Asisten</h3>
            <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Nama Asisten'); ?></p>
        </div>
        
        <div class="flex-grow overflow-y-auto">
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors duration-200 <?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="kelola_prak.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors duration-200 <?php echo ($activePage == 'kelola_prak') ? $activeClass : $inactiveClass; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                            <span>Kelola Praktikum</span>
                        </a>
                    </li>
                    <li>
                        <a href="kelola_modul.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors duration-200 <?php echo ($activePage == 'kelola_modul') ? $activeClass : $inactiveClass; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0A2.25 2.25 0 016.637 8.25l.857 6a2.25 2.25 0 012.227 1.932H14.28a2.25 2.25 0 012.227-1.932l.857-6a2.25 2.25 0 012.137-1.526m-16.5 0h16.5" /></svg>
                            <span>Kelola Modul</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan_masuk.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors duration-200 <?php echo ($activePage == 'laporan') ? $activeClass : $inactiveClass; ?>">
                             <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z" /></svg>
                            <span>Laporan Masuk</span>
                        </a>
                    </li>
                     <li>
                        <a href="kelola_pengguna.php" class="flex items-center px-3 py-2.5 rounded-md transition-colors duration-200 <?php echo ($activePage == 'kelola_pengguna') ? $activeClass : $inactiveClass; ?>">
                           <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.24.232-.487.335-.737m-3.058 3.07A3.375 3.375 0 006 6.75a3.375 3.375 0 00-3.375 3.375c0 1.113.285 2.16.786 3.07M9 4.5a3.375 3.375 0 00-3.375 3.375c0 1.621.832 3.024 2.089 3.868" /></svg>
                           <span>Kelola Pengguna</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="p-4 border-t border-gray-700">
             <a href="../logout.php" class="flex items-center justify-center w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            </header>



