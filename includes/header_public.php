// SIMPRAK/includes/header_public.php (Contoh sederhana)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SIMPRAK'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> /* custom CSS */ </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold text-gray-800">SIMPRAK</a>
            <div class="space-x-4">
                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" onclick="openModal('loginModal')">Login</button>
                <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300" onclick="openModal('registerModal')">Registrasi</button>
            </div>
        </div>
    </nav>
    <div class="container mx-auto mt-8 p-4">
    ```
Dan untuk `cari_praktikum.php` Anda akan menggunakan:
```php
<?php
// ... (logika php) ...
// require_once '../includes/header_public.php'; // Ganti header
require_once 'templates/header_mahasiswa.php'; // <-- Jika tetap pakai header mahasiswa

// ... (HTML body) ...

// require_once '../includes/footer_public.php'; // Ganti footer
require_once 'templates/footer_mahasiswa.php'; // <-- Jika tetap pakai footer mahasiswa
?>