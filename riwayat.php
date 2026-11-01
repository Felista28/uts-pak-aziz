<?php
// riwayat.php - Menampilkan riwayat transaksi kasir dan tanggal transaksi
session_start(); // Memulai session
require_once 'koneksi.php'; // Memuat file koneksi database

// Memastikan kasir sudah terautentikasi
if (!isset($_SESSION['karyawan_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Query JOIN untuk mengambil data header transaksi dan rincian item belanja
$sql = "SELECT 
            t.kode_transaksi,
            t.nama_pembeli,
            t.nama_kasir,
            t.total_bayar,
            t.tanggal_transaksi,
            d.kode_produk,
            d.nama_produk,
            d.tipe_kulit,
            d.harga_satuan,
            d.jumlah,
            d.subtotal
        FROM transaksi t
        JOIN detail_transaksi d ON t.kode_transaksi = d.kode_transaksi
        ORDER BY t.tanggal_transaksi DESC, t.id DESC";

$stmt = $pdo->query($sql);
$riwayat_raw = $stmt->fetchAll();

// Pengelompokan data transaksi berdasarkan 'kode_transaksi'
$riwayat = [];
foreach ($riwayat_raw as $row) {
    $kode = $row['kode_transaksi'];
    if (!isset($riwayat[$kode])) {
        $riwayat[$kode] = [
            'nama_pembeli'      => $row['nama_pembeli'],
            'nama_kasir'        => $row['nama_kasir'],
            'total_bayar'       => $row['total_bayar'],
            'tanggal_transaksi' => $row['tanggal_transaksi'],
            'items'             => []
        ];
    }
    $riwayat[$kode]['items'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrSide Beauty - Riwayat Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-urside-pink { background-color: #fff0f3; }
        .bg-urside-brown { background-color: #5c3d2e; }
        .text-urside-brown { color: #5c3d2e; }
        .border-urside-pink { border-color: #f4acb7; }
    </style>
</head>
<body class="bg-urside-pink min-h-screen p-6">

    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 border border-urside-pink">
        
        <!-- Header Halaman Riwayat -->
        <div class="flex flex-col sm:flex-row justify-between items-center pb-4 mb-6 border-b">
            <div>
                <h1 class="text-2xl font-black text-urside-brown">Riwayat Transaksi UrSide</h1>
                <p class="text-xs text-gray-500">Laporan Penjualan & Detail Pembelian Pelanggan</p>
            </div>
            <a href="index.php" class="mt-3 sm:mt-0 bg-urside-brown text-white px-4 py-2 text-xs font-bold rounded-lg hover:bg-opacity-90">
                ← Kembali ke Kasir
            </a>
        </div>

        <!-- Notifikasi Transaksi Sukses -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3 rounded mb-6 text-xs">
                ✅ <strong>Transaksi Berhasil!</strong> Struk dengan Kode <strong><?= htmlspecialchars($_GET['trx']); ?></strong> telah disimpan dan stok produk telah otomatis berkurang melalui trigger.
            </div>
        <?php endif; ?>

        <!-- Daftar Kartu Riwayat Transaksi -->
        <div class="space-y-6">
            <?php if (count($riwayat) > 0): ?>
                <?php foreach ($riwayat as $kode_trx => $trx): ?>
                    <div class="border border-pink-200 rounded-xl overflow-hidden shadow-sm">
                        <!-- Header Kartu Struk (Menampilkan Kode Struk & Tanggal Transaksi) -->
                        <div class="bg-pink-50 p-3 flex flex-wrap justify-between items-center text-xs border-b border-pink-200">
                            <div>
                                Kode Struk: <span class="font-mono font-bold text-pink-800 text-sm"><?= htmlspecialchars($kode_trx); ?></span>
                                <span class="ml-3 text-gray-500 font-semibold">📅 <?= date('d/m/Y H:i', strtotime($trx['tanggal_transaksi'])); ?></span>
                            </div>
                            <div class="space-x-4">
                                <span>Pembeli: <strong class="text-urside-brown"><?= htmlspecialchars($trx['nama_pembeli']); ?></strong></span>
                                <span>Kasir: <strong><?= htmlspecialchars($trx['nama_kasir']); ?></strong></span>
                            </div>
                        </div>

                        <!-- Tabel Rincian Item Barang Pembelian -->
                        <div class="p-3 overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="text-gray-500 border-b">
                                        <th class="py-1">Kode Produk</th>
                                        <th class="py-1">Nama Produk</th>
                                        <th class="py-1">Tipe Kulit</th>
                                        <th class="py-1">Harga Satuan</th>
                                        <th class="py-1">Banyak Dibeli</th>
                                        <th class="py-1 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <?php foreach ($trx['items'] as $item): ?>
                                        <tr>
                                            <td class="py-2 font-mono text-gray-600"><?= htmlspecialchars($item['kode_produk']); ?></td>
                                            <td class="py-2 font-bold text-gray-800"><?= htmlspecialchars($item['nama_produk']); ?></td>
                                            <td class="py-2">
                                                <span class="bg-pink-100 text-pink-800 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                                    <?= htmlspecialchars($item['tipe_kulit']); ?>
                                                </span>
                                            </td>
                                            <td class="py-2">Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td class="py-2 font-bold"><?= $item['jumlah']; ?> unit</td>
                                            <td class="py-2 text-right font-bold text-pink-700">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Tagihan Pembayaran -->
                        <div class="bg-gray-50 p-3 text-right text-xs border-t font-bold text-urside-brown">
                            Total Bayar: <span class="text-sm text-pink-700">Rp <?= number_format($trx['total_bayar'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-400 py-8 text-xs">Belum ada riwayat transaksi yang tercatat.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>