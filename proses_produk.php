<?php
// proses_produk.php - Eksekusi logika CRUD Produk
session_start(); // Memulai session
require_once 'koneksi.php'; // Memuat file koneksi database

// Memastikan hanya karyawan terautentikasi yang dapat mengakses
if (!isset($_SESSION['karyawan_logged_in'])) {
    header("Location: login.php");
    exit();
}

$aksi = isset($_REQUEST['aksi']) ? $_REQUEST['aksi'] : '';

// 1. PROSES TAMBAH & EDIT PRODUK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($aksi === 'tambah' || $aksi === 'edit')) {
    $id          = isset($_POST['id']) ? $_POST['id'] : null;
    $kode_produk = trim($_POST['kode_produk']);
    $nama_produk = trim($_POST['nama_produk']);
    $tipe_kulit  = trim($_POST['tipe_kulit']);
    $harga       = floatval($_POST['harga']);
    $stok        = intval($_POST['stok']);

    if ($aksi === 'tambah') {
        // Query Insert Produk Baru ke Database
        $sql = "INSERT INTO produk (kode_produk, nama_produk, tipe_kulit, harga, stok) 
                VALUES (:kode, :nama, :tipe, :harga, :stok)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'kode'  => $kode_produk,
            'nama'  => $nama_produk,
            'tipe'  => $tipe_kulit,
            'harga' => $harga,
            'stok'  => $stok
        ]);
    } else if ($aksi === 'edit') {
        // Query Update Produk Berdasarkan ID
        $sql = "UPDATE produk SET kode_produk = :kode, nama_produk = :nama, tipe_kulit = :tipe, 
                harga = :harga, stok = :stok WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'kode'  => $kode_produk,
            'nama'  => $nama_produk,
            'tipe'  => $tipe_kulit,
            'harga' => $harga,
            'stok'  => $stok,
            'id'    => $id
        ]);
    }

    // Alihkan kembali ke halaman kasir utama
    header("Location: index.php");
    exit();
}

// 2. PROSES HAPUS PRODUK
if ($aksi === 'hapus' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Query Hapus Produk Berdasarkan ID
    $stmt = $pdo->prepare("DELETE FROM produk WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: index.php");
    exit();
}
?>