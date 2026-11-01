<?php
// logout.php - Menghapus sesi login dan mengarahkan kembali ke login.php
session_start(); // Memulai sesi

$_SESSION = array(); // Mengosongkan variabel sesi
session_destroy();   // Menghancurkan seluruh data sesi

// Alihkan pengguna ke halaman login
header("Location: login.php");
exit();
?>