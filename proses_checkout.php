<?php
// proses_checkout.php - Pemroses checkout transaksi dan trigger pengurangan stok
session_start(); // Memulai session
require_once 'koneksi.php'; // Memuat koneksi database

// Memastikan kasir sudah login
if (!isset($_SESSION['karyawan_logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pembeli = trim($_POST['nama_pembeli']);
    $cart_json    = $_POST['cart_data'];
    $items        = json_decode($cart_json, true);

    // Validasi kelengkapan data checkout
    if (empty($nama_pembeli) || empty($items) || count($items) === 0) {
        die("Error: Data transaksi atau nama pembeli tidak valid.");
    }

    // Membangun kode transaksi unik (contoh: TRX-20261031-4829)
    $kode_transaksi = "TRX-" . date('Ymd') . "-" . rand(1000, 9999);
    $nama_kasir     = $_SESSION['nama_karyawan'];

    // Menghitung grand total bayar
    $total_bayar = 0;
    foreach ($items as $item) {
        $total_bayar += ($item['harga'] * $item['jumlah']);
    }

    try {
        // Memulai transaksi Database (DB Transaction)
        $pdo->beginTransaction();

        // 1. Memasukkan data utama ke tabel 'transaksi' (tanggal_transaksi terisi otomatis)
        $stmt_trx = $pdo->prepare("INSERT INTO transaksi (kode_transaksi, nama_pembeli, nama_kasir, total_bayar) 
                                   VALUES (:kode, :pembeli, :kasir, :total)");
        $stmt_trx->execute([
            'kode'    => $kode_transaksi,
            'pembeli' => $nama_pembeli,
            'kasir'   => $nama_kasir,
            'total'   => $total_bayar
        ]);

        // 2. Memasukkan rincian barang ke tabel 'detail_transaksi'
        // *** METODE TRIGGER (tg_kurangi_stok_otomatis) OTOMATIS BERJALAN SAAT INSERT DI BAWAH INI ***
        $stmt_detail = $pdo->prepare("INSERT INTO detail_transaksi 
            (kode_transaksi, kode_produk, nama_produk, tipe_kulit, harga_satuan, jumlah, subtotal) 
            VALUES (:kode_trx, :kode_p, :nama_p, :tipe, :harga, :jumlah, :subtotal)");

        foreach ($items as $item) {
            $subtotal = $item['harga'] * $item['jumlah'];
            
            $stmt_detail->execute([
                'kode_trx' => $kode_transaksi,
                'kode_p'   => $item['kode_produk'],
                'nama_p'   => $item['nama_produk'],
                'tipe'     => $item['tipe_kulit'],
                'harga'    => $item['harga'],
                'jumlah'   => $item['jumlah'],
                'subtotal' => $subtotal
            ]);
        }

        // Commit seluruh perintah database jika sukses tanpa eror
        $pdo->commit();

        // Alihkan ke halaman riwayat transaksi
        header("Location: riwayat.php?status=sukses&trx=" . urlencode($kode_transaksi));
        exit();

    } catch (Exception $e) {
        // Membatalkan seluruh perubahan jika terjadi kegagalan
        $pdo->rollBack();
        die("Transaksi Gagal Diproses: " . $e->getMessage());
    }
}
?>