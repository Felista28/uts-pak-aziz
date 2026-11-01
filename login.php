<?php
// login.php - Halaman autentikasi login karyawan
session_start(); // Mengaktifkan sesi PHP
require_once 'koneksi.php'; // Memanggil file koneksi database

// Jika pengguna sudah dalam keadaan login, alihkan langsung ke halaman kasir
if (isset($_SESSION['karyawan_logged_in'])) {
    header("Location: index.php");
    exit();
}

$error_message = ""; // Variabel penampung pesan kesalahan

// Memproses data kiriman form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // Mengambil dan membersihkan input username
    $password = trim($_POST['password']); // Mengambil dan membersihkan input password

    if (!empty($username) && !empty($password)) {
        // Query prepared statement untuk mencari akun karyawan di database
        $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $karyawan = $stmt->fetch();

        // Validasi keberadaan data karyawan dan kecocokan password
        if ($karyawan && $karyawan['password'] === $password) {
            // Menyimpan data login ke dalam Sesi
            $_SESSION['karyawan_logged_in'] = true;
            $_SESSION['karyawan_id']        = $karyawan['id'];
            $_SESSION['nama_karyawan']      = $karyawan['nama_karyawan'];
            $_SESSION['username']           = $karyawan['username'];

            // Alihkan ke halaman dashboard kasir utama
            header("Location: index.php");
            exit();
        } else {
            // Pesan penolakan jika akun belum dimasukkan oleh developer
            $error_message = "Akun tidak ditemukan atau password salah! Apabila data belum dimasukkan, mintalah Developer untuk menyuntikkan akun ke dalam database.";
        }
    } else {
        $error_message = "Harap isi username dan password secara lengkap!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrSide Beauty POS - Login Kasir</title>
    <!-- Memuat Tailwind CSS untuk penataan gaya -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Pengaturan tema warna UrSide (Pink & Coklat) */
        .bg-urside-pink { background-color: #fff0f3; }
        .text-urside-brown { color: #5c3d2e; }
        .bg-urside-brown { background-color: #5c3d2e; }
        .border-urside-pink { border-color: #f4acb7; }
    </style>
</head>
<body class="bg-urside-pink min-h-screen flex items-center justify-center p-4">

    <!-- Container Kartu Form Login -->
    <div class="bg-white rounded-2xl shadow-xl border border-urside-pink w-full max-w-md p-8">
        <!-- Logo & Judul Aplikasi -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-urside-brown tracking-wide">UrSide</h1>
            <p class="text-sm font-medium text-pink-600 mt-1">Beauty & Skincare Cashier Portal</p>
        </div>

        <!-- Notifikasi Kesalahan / Peringatan Suntikan Developer -->
        <?php if (!empty($error_message)): ?>
            <div class="bg-pink-50 border-l-4 border-pink-500 text-pink-800 p-3 rounded mb-5 text-xs leading-relaxed">
                <strong>Akses Ditolak:</strong> <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Form Login Karyawan -->
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-urside-brown uppercase mb-1">Username Karyawan</label>
                <input type="text" name="username" required placeholder="Masukkan username (contoh: kasir2)"
                       class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-urside-brown uppercase mb-1">Password</label>
                <input type="password" name="password" required placeholder=""
                       class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-urside-brown text-white font-bold text-sm rounded-lg shadow-md hover:bg-opacity-90 transition duration-200">
                MASUK KE APLIKASI
            </button>
        </form>
    </div>

</body>
</html>