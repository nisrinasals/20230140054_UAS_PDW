<?php
// mahasiswa/templates/header_mahasiswa.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logika untuk mengecek status login dan peran pengguna
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa';
$userName = htmlspecialchars($_SESSION['nama'] ?? 'Pengunjung');

// Cek jika ada role lain (misal: asisten) mencoba akses, arahkan ke panel mereka
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'asisten') {
    header("Location: ../asisten/dashboard.php");
    exit();
}

// Definisikan halaman publik yang bisa diakses tanpa login
$current_page_name = basename($_SERVER['PHP_SELF'], '.php');
$public_pages = ['cari_praktikum', 'login', 'register']; // Tambahkan login/register jika ada di folder ini

// Jika user belum login DAN halaman ini BUKAN halaman publik, redirect ke login
if (!$isLoggedIn && !in_array($current_page_name, $public_pages)) {
    header("Location: ../login.php"); 
    exit();
}

// Menyiapkan variabel untuk judul halaman dan menandai link aktif
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? '';

// Menyiapkan class untuk link aktif dan tidak aktif
$activeClass = 'bg-gray-700 text-white';
$inactiveClass = 'text-gray-400 hover:bg-gray-600 hover:text-white';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - SIMPRAK Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-link.active {
            background-color: #4A5568;
            color: #FFFFFF;
            font-weight: bold;
        }
        /* Style untuk sidebar responsif */
        @media (max-width: 767px) {
            .sidebar-collapsed { display: none; }
            .sidebar-expanded { display: flex; position: fixed; top: 0; left: 0; height: 100%; z-index: 40; }
        }
        .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 30; }
        .overlay.active { display: block; }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-50">
    <div id="sidebar-overlay" class="overlay"></div>

    <aside id="sidebar" class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0 sidebar-collapsed md:flex">
        <div class="p-5 text-center border-b border-gray-700">
            <h3 class="text-xl font-semibold">SIMPRAK</h3>
            <p class="text-sm text-gray-400 mt-1">Panel Mahasiswa</p>
        </div>
        
        <div class="flex-grow overflow-y-auto">
            <nav class="p-4">
                <ul class="space-y-2">
                    <?php if ($isLoggedIn): ?>
                        <li><a href="dashboard.php" class="flex items-center px-3 py-2.5 rounded-md <?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            <span>Dashboard</span>
                        </a></li>
                        <li><a href="my_praktikum.php" class="flex items-center px-3 py-2.5 rounded-md <?php echo ($activePage == 'my_praktikum') ? $activeClass : $inactiveClass; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2.25 2.25 0 003.072 0l-.548-1.096a.75.75 0 01.275-1.003l.548-.33a2.25 2.25 0 000-3.996l-.548-.33a.75.75 0 01-.275-1.003l.548-1.096a2.25 2.25 0 00-3.072 0l-.548 1.096a.75.75 0 01-1.003.275l-.33-.548a2.25 2.25 0 00-3.996 0l-.33.548a.75.75 0 01-1.003-.275l-.548-1.096a2.25 2.25 0 00-3.072 0l.548 1.096a.75.75 0 01.275 1.003l.548.33a2.25 2.25 0 000 3.996l.548.33a.75.75 0 01.275 1.003l-.548 1.096a2.25 2.25 0 003.072 0l.548-1.096a.75.75 0 011.003-.275l.33.548a2.25 2.25 0 003.996 0l.33-.548a.75.75 0 011.003.275l.548 1.096z" /></svg>
                            <span>Praktikum Saya</span>
                        </a></li>
                    <?php endif; ?>
                    <li><a href="cari_praktikum.php" class="flex items-center px-3 py-2.5 rounded-md <?php echo ($activePage == 'cari_praktikum') ? $activeClass : $inactiveClass; ?>">
                        <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <span>Cari Praktikum</span>
                    </a></li>
                </ul>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-700">
             <?php if ($isLoggedIn): ?>
                <a href="../logout.php" class="flex items-center justify-center w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">
                    Logout
                </a>
             <?php else: ?>
                <a href="../login.php" class="flex items-center justify-center w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                    Login
                </a>
             <?php endif; ?>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <button id="sidebar-toggle" class="md:hidden text-gray-800">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <h1 class="text-xl font-semibold text-gray-700 ml-2 md:ml-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="text-gray-600 hidden md:block">
                <?php if ($isLoggedIn): ?>
                    Selamat datang, <span class="font-bold"><?php echo $userName; ?></span>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
