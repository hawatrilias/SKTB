<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

// DAFTAR KATEGORI yang tersedia
$kategori_list = ['Fresh Flower', 'Pipe Cleaner', 'Fake Flower', 'Lainnya'];

// =========================================================================
// LOGIKA FILTER PHP
// =========================================================================

// 1. Ambil kategori dari URL jika ada
$filter_kategori = $_GET['kategori'] ?? null;

// 2. Mulai query SQL
$sql = "SELECT * FROM produk";

// 3. Tambahkan filter kategori jika dipilih
if ($filter_kategori && in_array($filter_kategori, $kategori_list)) {
    // Penggunaan mysqli_real_escape_string untuk keamanan dasar (Walaupun prepared statement lebih disarankan)
    $safe_kategori = mysqli_real_escape_string($conn, $filter_kategori);
    $sql .= " WHERE kategori = '" . $safe_kategori . "'";
}

// 4. Tambahkan ORDER BY di akhir
$sql .= " ORDER BY id_produk DESC";

// 5. Eksekusi Query
$result = mysqli_query($conn, $sql);

// =========================================================================

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Produk - Floristy Muse</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .filter-form select {
            padding: 8px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="navbar">
            <div class="judul">
                <h2>Floristy <span>Muse</span></h2>
            </div>
            <ul class="link">
                <li><a href="admin.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="pemasukan.php"><i class="fas fa-arrow-up"></i> Pemasukan</a></li>
                <li><a href="pengeluaran.php"><i class="fas fa-arrow-down"></i> Pengeluaran</a></li>
                <li><a href="transaksi.php"><i class="fas fa-exchange-alt"></i> Transaksi</a></li>
                <li><a href="produk.php"><i class="fas fa-box"></i> Produk</a></li>
                <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
                <li><a href="pengguna.php"><i class="fas fa-user"></i> Pengguna</a></li>
                <li><a href="signout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </header>
        <main class="dashboard-container">
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3>Produk</h3>
                    <div style="margin-left: 30px; display:flex; gap: 10px;">
                        
                        <form id="filterForm" method="GET" action="produk.php" class="filter-form" style="display: flex; gap: 10px; align-items: center;">
                            <select name="kategori" id="kategori-filter">
                                <option value="" <?= !$filter_kategori ? 'selected' : '' ?>>Semua Kategori</option>
                                <?php foreach ($kategori_list as $kategori): ?>
                                    <option 
                                        value="<?= $kategori ?>" 
                                        <?= ($filter_kategori === $kategori) ? 'selected' : '' ?>>
                                        <?= $kategori ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
                            
                            <?php if ($filter_kategori): ?>
                                <a href="produk.php" class="filter-btn" style="text-decoration: none; padding: 7px 10px; border: 1px solid #ccc; background: #f0f0f0;">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </form>
                        <a href="tambah_produk.php" class="add-btn">Tambah Produk</a>
                    </div>
                </div>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th width="80">GAMBAR</th>
                            <th>NAMA PRODUK</th>
                            <th width="150">KATEGORI</th>
                            <th width="120">HARGA</th>
                            <th width="80">STOK</th>
                            <th width="150">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="product-img">
                                        <?php if (!empty($row['gambar'])): ?>
                                            <?php
                                            $imagePath = 'pict/' . $row['gambar'];
                                            if (file_exists($imagePath)) {
                                                // Catatan: Pastikan Anda memiliki CSS untuk .product-img img
                                                echo '<img src="' . $imagePath . '" alt="' . $row['nama_produk'] . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">';
                                            } else {
                                                echo '<div class="no-image"><i class="fas fa-image"></i></div>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="product-name">
                                        <div class="product-title"><?= $row['nama_produk']; ?></div>
                                        <?php if (!empty($row['deskripsi'])): ?>
                                            <div class="product-desc"><?= substr($row['deskripsi'], 0, 50) ?>...</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?= $row['kategori']; ?></span>
                                    </td>
                                    <td class="product-price">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span
                                            class="stock-badge <?= $row['stok'] > 10 ? 'in-stock' : ($row['stok'] > 0 ? 'low-stock' : 'out-of-stock') ?>">
                                            <?= $row['stok']; ?>
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <div class="action-btn">
                                            <a href="edit_produk.php?id=<?= $row['id_produk']; ?>" class="edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="hapus_produk.php?id=<?= $row['id_produk']; ?>" class="delete" title="Hapus"
                                                onclick="return confirm('Yakin hapus produk ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-table">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>Belum ada data produk</p>
                                        <a href="tambah_produk.php" class="add-btn" style="margin-top: 15px;">Tambah Produk</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-title">
                <h3>Floristy <br> <span>Muse</span></h3>
            </div>
            <div class="footer-links">
                <h4>Menu</h4>
                <ul>
                    <li><a href="admin.php">Dashboard</a></li>
                    <li><a href="pemasukan.php">Pemasukan</a></li>
                    <li><a href="pengeluaran.php">Pengeluaran</a></li>
                    <li><a href="transaksi.php">Transaksi</a></li>
                    <li><a href="produk.php">Produk</a></li>
                    <li><a href="laporan.php">Laporan</a></li>
                    <li><a href="pengguna.php">Pengguna</a></li>
                </ul>
            </div>
            <div class="contact">
                <div class="footer-contact">
                    <h4>Kontak</h4>
                    <p>📍 Surakarta</p>
                    <p>📞 0812-3456-7890</p>
                    <p>✉ floristymuse@gmail.com</p>
                </div><br>
    
                <div class="footer-social">
                    <h4>Ikuti Kami</h4>
                    <a href="#">Instagram</a><br>
                    <a href="#">TikTok</a><br>
                    <a href="#">WhatsApp</a>
                </div>
            </div>

        </div>

        <div class="footer-bottom" align="center">
            <p>© 2025 Floristy Muse · All Rights Reserved.</p>
        </div>
    </footer>
    <script>
        // ... KODE JAVASCRIPT UNTUK ACTIVE LINK TETAP SAMA ...
        const links = document.querySelectorAll(".link li a");

        const activePage = localStorage.getItem("activeLink");
        if (activePage) {
            links.forEach(link => {
                if (link.getAttribute("href") === activePage) {
                    link.classList.add("active");
                }
            });
        }

        links.forEach(link => {
            link.addEventListener("click", () => {
                links.forEach(l => l.classList.remove("active"));
                link.classList.add("active");
                localStorage.setItem("activeLink", link.getAttribute("href"));
            });
        });
    </script>
</body>

</html>