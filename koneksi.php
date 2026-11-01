<?php
// koneksi.php - Mengatur koneksi basis data menggunakan PDO

$host     = "localhost";    // Nama host server MySQL
$user     = "root";         // Username akun MySQL
$pass     = "";             // Password akun MySQL (default XAMPP kosong)
$dbname   = "urside";    // Nama database aplikasi UrSide

try {
    // Membuka koneksi PDO dengan pengaturan penanganan exception dan fetching asosiatif
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan eror berbentuk Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis berformat Array Asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statement asli MySQL
    ]);
} catch (PDOException $e) {
    // Menampilkan pesan jika koneksi ke database gagal
    die("Koneksi ke database UrSide gagal: " . $e->getMessage());
}
?>