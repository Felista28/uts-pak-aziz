<?php
// index.php - Dashboard utama transaksi kasir UrSide POS
session_start(); // Memulai session
require_once 'koneksi.php'; // Memuat file koneksi database

// Proteksi halaman: Mengalihkan pengguna ke login.php jika belum terautentikasi
if (!isset($_SESSION['karyawan_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Menangkap parameter pencarian dan filter tipe kulit dari permintaan GET
$search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_skin  = isset($_GET['tipe_kulit']) ? trim($_GET['tipe_kulit']) : '';

// Menyusun SQL dasar untuk mengambil seluruh daftar produk
$sql = "SELECT * FROM produk WHERE 1=1";
$params = [];

// Menambahkan kondisi query jika ada kata kunci pencarian
if (!empty($search_query)) {
    $sql .= " AND (nama_produk LIKE :search OR kode_produk LIKE :search)";
    $params['search'] = "%$search_query%";
}

// Menambahkan kondisi query jika ada filter tipe kulit spesifik
if (!empty($selected_skin) && $selected_skin !== 'Semua') {
    $sql .= " AND tipe_kulit = :tipe_kulit";
    $params['tipe_kulit'] = $selected_skin;
}

$sql .= " ORDER BY id DESC"; // Mengurutkan dari produk terbaru
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_produk = $stmt->fetchAll(); // Menyimpan hasil data produk dalam array
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrSide Beauty - Aplikasi Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-urside-pink-light { background-color: #fff0f3; }
        .bg-urside-brown { background-color: #5c3d2e; }
        .bg-urside-brown-dark { background-color: #3d2314; }
        .text-urside-brown { color: #5c3d2e; }
        .border-urside-pink { border-color: #f4acb7; }
    </style>
</head>
<body class="bg-urside-pink-light min-h-screen font-sans flex flex-col">

    <!-- Header Navigation Bar -->
    <header class="bg-urside-brown text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-black tracking-wider text-pink-200">UrSide</span>
            </div>
            
            <div class="flex items-center space-x-6 text-sm">
                <div>
                    Kasir: <span class="font-bold text-pink-200"><?= htmlspecialchars($_SESSION['nama_karyawan']); ?></span>
                </div>
                <a href="riwayat.php" class="bg-pink-600 hover:bg-pink-700 text-white px-3 py-1.5 rounded-lg font-medium text-xs transition">
                    Riwayat Transaksi
                </a>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg font-medium text-xs transition">
                    Keluar
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Utama Aplikasi Kasir -->
    <main class="max-w-7xl mx-auto px-4 py-6 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- SISI KIRI: Katalog Produk, Filter Tipe Kulit, & CRUD -->
        <section class="lg:col-span-8 space-y-5">
            
            <!-- Pencarian & Tombol Tambah Produk (CRUD) -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-urside-pink flex flex-col sm:flex-row gap-3 justify-between items-center">
                <!-- Form Pencarian -->
                <form action="index.php" method="GET" class="flex items-center w-full sm:w-auto flex-1 gap-2">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query); ?>" 
                           placeholder="Cari kode atau nama produk skincare..."
                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                    <?php if (!empty($selected_skin)): ?>
                        <input type="hidden" name="tipe_kulit" value="<?= htmlspecialchars($selected_skin); ?>">
                    <?php endif; ?>
                    <button type="submit" class="bg-urside-brown text-white px-4 py-2 text-sm rounded-lg hover:bg-urside-brown-dark">
                        Cari
                    </button>
                </form>

                <!-- Tombol Buka Modal Tambah Produk -->
                <button onclick="bukaModalProduk()" class="w-full sm:w-auto bg-pink-600 text-white px-4 py-2 text-sm font-bold rounded-lg hover:bg-pink-700 transition">
                    + Tambah Produk
                </button>
            </div>

            <!-- Filter Kategori Tipe Kulit -->
            <div class="flex flex-wrap gap-2 items-center bg-white p-3 rounded-xl border border-urside-pink">
                <span class="text-xs font-bold text-urside-brown uppercase mr-2">Tipe Kulit:</span>
                <?php
                $tipe_list = ['Semua', 'Normal', 'Kering', 'Berminyak', 'Kombinasi', 'Sensitif'];
                foreach ($tipe_list as $tipe):
                    $active = ($selected_skin === $tipe || (empty($selected_skin) && $tipe === 'Semua'));
                    $btn_class = $active 
                        ? "bg-urside-brown text-white font-bold" 
                        : "bg-pink-50 text-urside-brown hover:bg-pink-100 border border-pink-200";
                ?>
                    <a href="index.php?tipe_kulit=<?= urlencode($tipe); ?>&search=<?= urlencode($search_query); ?>" 
                       class="px-3 py-1 rounded-full text-xs font-medium transition <?= $btn_class; ?>">
                        <?= $tipe; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Grid Tampilan Produk -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php if (count($daftar_produk) > 0): ?>
                    <?php foreach ($daftar_produk as $p): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-pink-200 p-4 flex flex-col justify-between hover:shadow-md transition">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[10px] text-gray-400 font-mono"><?= htmlspecialchars($p['kode_produk']); ?></span>
                                    <span class="bg-pink-100 text-pink-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        <?= htmlspecialchars($p['tipe_kulit']); ?>
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-urside-brown leading-tight"><?= htmlspecialchars($p['nama_produk']); ?></h3>
                                <p class="text-base font-extrabold text-pink-700 mt-2">Rp <?= number_format($p['harga'], 0, ',', '.'); ?></p>
                            </div>

                            <!-- Indikator Stok & Tombol Aksi Keranjang / CRUD -->
                            <div class="mt-4 pt-2 border-t flex justify-between items-center text-xs">
                                <span class="<?= $p['stok'] > 0 ? 'text-gray-600' : 'text-red-600 font-bold'; ?>">
                                    Stok: <strong><?= $p['stok']; ?></strong>
                                </span>

                                <div class="flex items-center space-x-1">
                                    <!-- Tombol Edit Produk -->
                                    <button onclick='bukaModalEdit(<?= json_encode($p); ?>)' class="bg-amber-100 text-amber-800 p-1 rounded hover:bg-amber-200 text-[10px]">
                                        ✏️
                                    </button>
                                    <!-- Tombol Hapus Produk -->
                                    <a href="proses_produk.php?aksi=hapus&id=<?= $p['id']; ?>" onclick="return confirm('Hapus produk ini?')" class="bg-red-100 text-red-700 p-1 rounded hover:bg-red-200 text-[10px]">
                                        🗑️
                                    </a>
                                    <!-- Tombol Masuk Keranjang -->
                                    <?php if ($p['stok'] > 0): ?>
                                        <button onclick='tambahKeKeranjang(<?= json_encode($p); ?>)' class="bg-urside-brown text-white px-2.5 py-1 rounded text-xs font-bold hover:bg-urside-brown-dark">
                                            + Beli
                                        </button>
                                    <?php else: ?>
                                        <span class="text-[10px] bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded">Habis</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white p-8 text-center text-gray-500 rounded-xl border">
                        Tidak ada produk yang ditemukan.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- SISI KANAN: Keranjang Kasir & Form Checkout -->
        <section class="lg:col-span-4 bg-white p-5 rounded-xl shadow-md border border-urside-pink flex flex-col justify-between h-fit sticky top-20">
            <div>
                <h2 class="text-lg font-bold text-urside-brown border-b pb-2 mb-3 flex justify-between items-center">
                    <span>🛍️ Keranjang Belanja</span>
                    <button onclick="kosongkanKeranjang()" class="text-xs text-red-500 hover:underline">Reset</button>
                </h2>

                <!-- Form Checkout Pembayaran -->
                <form id="formCheckout" action="proses_checkout.php" method="POST">
                    <!-- Input Nama Pembeli -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-urside-brown uppercase mb-1">Nama Pembeli *</label>
                        <input type="text" name="nama_pembeli" id="nama_pembeli" required placeholder="Input nama pelanggan..."
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-pink-300 focus:outline-none">
                    </div>

                    <!-- Container Item Keranjang -->
                    <div id="keranjangContainer" class="space-y-3 max-h-60 overflow-y-auto pr-1 mb-4 divide-y">
                        <p class="text-xs text-gray-400 text-center py-4">Keranjang masih kosong.</p>
                    </div>

                    <!-- Input Tersembunyi Penyimpan JSON Keranjang -->
                    <input type="hidden" name="cart_data" id="cartDataInput">

                    <!-- Ringkasan Total Tagihan -->
                    <div class="border-t pt-3 space-y-2">
                        <div class="flex justify-between text-sm font-bold text-urside-brown">
                            <span>Total Harga:</span>
                            <span id="labelTotalBayar" class="text-pink-700 text-base">Rp 0</span>
                        </div>

                        <!-- Tombol Submit Checkout -->
                        <button type="submit" id="btnCheckout" disabled
                                class="w-full py-2.5 bg-urside-brown text-white font-bold text-sm rounded-lg shadow disabled:opacity-50 hover:bg-urside-brown-dark transition">
                            SELESAIKAN PEMBAYARAN
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

    <!-- MODAL POPUP: Form Tambah & Edit Produk (CRUD) -->
    <div id="modalProduk" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative border-2 border-pink-200">
            <h3 id="modalTitle" class="text-lg font-bold text-urside-brown mb-4">Tambah Produk Baru</h3>
            
            <form action="proses_produk.php" method="POST" class="space-y-3">
                <input type="hidden" name="aksi" id="formAksi" value="tambah">
                <input type="hidden" name="id" id="formId">

                <div>
                    <label class="block text-xs font-bold text-gray-700">Kode Produk</label>
                    <input type="text" name="kode_produk" id="formKode" required placeholder="misal: URS-006" class="w-full p-2 border rounded text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700">Nama Produk</label>
                    <input type="text" name="nama_produk" id="formNama" required placeholder="Nama skincare..." class="w-full p-2 border rounded text-xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700">Tipe Kulit</label>
                    <select name="tipe_kulit" id="formTipe" required class="w-full p-2 border rounded text-xs">
                        <option value="Normal">Normal</option>
                        <option value="Kering">Kering</option>
                        <option value="Berminyak">Berminyak</option>
                        <option value="Kombinasi">Kombinasi</option>
                        <option value="Sensitif">Sensitif</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Harga (Rp)</label>
                        <input type="number" name="harga" id="formHarga" required min="0" step="500" class="w-full p-2 border rounded text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Stok Awal</label>
                        <input type="number" name="stok" id="formStok" required min="0" class="w-full p-2 border rounded text-xs">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3">
                    <button type="button" onclick="tutupModalProduk()" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-bold rounded">Batal</button>
                    <button type="submit" class="px-4 py-1.5 bg-pink-600 text-white text-xs font-bold rounded hover:bg-pink-700">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT JAVASCRIPT: Pengelolaan Keranjang Belanja & Modal CRUD -->
    <script>
        let keranjang = [];

        // Menambahkan barang pilihan ke array keranjang
        function tambahKeKeranjang(produk) {
            let item = keranjang.find(i => i.kode_produk === produk.kode_produk);
            if (item) {
                if (item.jumlah < produk.stok) {
                    item.jumlah++;
                } else {
                    alert('Batas stok maksimum tercapai untuk produk ini!');
                }
            } else {
                keranjang.push({
                    kode_produk: produk.kode_produk,
                    nama_produk: produk.nama_produk,
                    tipe_kulit: produk.tipe_kulit,
                    harga: parseFloat(produk.harga),
                    stok_max: produk.stok,
                    jumlah: 1
                });
            }
            updateTampilanKeranjang();
        }

        // Memperbarui tampilan UI keranjang belanja dan total harga
        function updateTampilanKeranjang() {
            const container = document.getElementById('keranjangContainer');
            const labelTotal = document.getElementById('labelTotalBayar');
            const inputCartData = document.getElementById('cartDataInput');
            const btnCheckout = document.getElementById('btnCheckout');

            if (keranjang.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Keranjang masih kosong.</p>';
                labelTotal.innerText = 'Rp 0';
                inputCartData.value = '';
                btnCheckout.disabled = true;
                return;
            }

            let html = '';
            let totalBayar = 0;

            keranjang.forEach((item, index) => {
                let subtotal = item.harga * item.jumlah;
                totalBayar += subtotal;

                html += `
                <div class="pt-2 flex justify-between items-center text-xs">
                    <div class="flex-1 pr-2">
                        <div class="font-bold text-gray-800">${item.nama_produk}</div>
                        <div class="text-[10px] text-pink-600 font-mono">${item.kode_produk} (${item.tipe_kulit})</div>
                        <div class="text-[10px] text-gray-500">Rp ${item.harga.toLocaleString('id-ID')} x ${item.jumlah}</div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="ubahJumlah(${index}, -1)" class="px-1.5 py-0.5 bg-gray-200 font-bold rounded">-</button>
                        <span class="font-bold px-1">${item.jumlah}</span>
                        <button type="button" onclick="ubahJumlah(${index}, 1)" class="px-1.5 py-0.5 bg-gray-200 font-bold rounded">+</button>
                        <button type="button" onclick="hapusItem(${index})" class="text-red-500 ml-1 font-bold">✕</button>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
            labelTotal.innerText = 'Rp ' + totalBayar.toLocaleString('id-ID');
            inputCartData.value = JSON.stringify(keranjang);
            btnCheckout.disabled = false;
        }

        // Mengubah kuantitas jumlah item di keranjang
        function ubahJumlah(index, delta) {
            let item = keranjang[index];
            if (delta > 0 && item.jumlah >= item.stok_max) {
                alert('Stok tidak mencukupi!');
                return;
            }
            item.jumlah += delta;
            if (item.jumlah <= 0) keranjang.splice(index, 1);
            updateTampilanKeranjang();
        }

        // Menghapus item tertentu dari keranjang
        function hapusItem(index) {
            keranjang.splice(index, 1);
            updateTampilanKeranjang();
        }

        // Mengosongkan seluruh keranjang
        function kosongkanKeranjang() {
            keranjang = [];
            updateTampilanKeranjang();
        }

        // Buka Modal untuk Tambah Produk
        function bukaModalProduk() {
            document.getElementById('modalTitle').innerText = 'Tambah Produk Baru';
            document.getElementById('formAksi').value = 'tambah';
            document.getElementById('formId').value = '';
            document.getElementById('formKode').value = '';
            document.getElementById('formNama').value = '';
            document.getElementById('formHarga').value = '';
            document.getElementById('formStok').value = '';
            document.getElementById('modalProduk').classList.remove('hidden');
            document.getElementById('modalProduk').classList.add('flex');
        }

        // Buka Modal untuk Edit Produk
        function bukaModalEdit(p) {
            document.getElementById('modalTitle').innerText = 'Edit Produk UrSide';
            document.getElementById('formAksi').value = 'edit';
            document.getElementById('formId').value = p.id;
            document.getElementById('formKode').value = p.kode_produk;
            document.getElementById('formNama').value = p.nama_produk;
            document.getElementById('formTipe').value = p.tipe_kulit;
            document.getElementById('formHarga').value = p.harga;
            document.getElementById('formStok').value = p.stok;
            document.getElementById('modalProduk').classList.remove('hidden');
            document.getElementById('modalProduk').classList.add('flex');
        }

        // Tutup Modal Form Produk
        function tutupModalProduk() {
            document.getElementById('modalProduk').classList.add('hidden');
            document.getElementById('modalProduk').classList.remove('flex');
        }
    </script>
</body>
</html>