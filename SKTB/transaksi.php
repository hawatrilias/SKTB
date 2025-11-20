<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

// Ambil semua data transaksi
$sql = "SELECT * FROM transaksi ORDER BY tanggal DESC";
$result = mysqli_query($conn, $sql);

// Filter 
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

$sql = "SELECT * FROM transaksi WHERE 1";

if ($bulan != '') {
    $sql .= " AND MONTH(tanggal) = '$bulan'";
}

if ($tahun != '') {
    $sql .= " AND YEAR(tanggal) = '$tahun'";
}

$sql .= " ORDER BY tanggal DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi - Floristy Muse</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container">

        <!-- Header -->
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

        <!-- Konten -->
        <main class="dashboard-container">
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3>Transaksi</h3>
                    <div style="margin-left: 30px; display:flex; gap: 10px;">
                        <button class="filter-btn" id="openFilter"><i class="fas fa-filter"></i> Filter</button>
                        <a href="tambah_transaksi.php" class="add-btn">Tambah Transaksi</a>
                    </div>
                </div>
                <table class="trans-table">
                    <thead>
                        <tr>
                            <th width="120">TANGGAL</th>
                            <th width="80">NAMA PELANGGAN</th>
                            <th width="120">PRODUK</th>
                            <th width="80">KATEGORI</th>
                            <th width="80">JUMLAH</th>
                            <th width="80">TOTAL BAYAR</th>
                            <th width="80">METODE</th>
                            <th width="80">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['tanggal']; ?></td>
                                <td><?= $row['nama_pelanggan']; ?></td>
                                <td><?= $row['produk']; ?></td>
                                <td><?= $row['kategori']; ?></td>
                                <td><?= $row['jumlah']; ?></td>
                                <td>Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                                <td><?= $row['metode']; ?></td>
                                <td class="action-btn">
                                    <a href="edit_transaksi.php?id=<?= $row['id_transaksi']; ?>" class="edit"><i
                                            class="fas fa-edit"></i></a>
                                    <a href="hapus_transaksi.php?id=<?= $row['id_transaksi']; ?>" class="delete"
                                        onclick="return confirm('Yakin hapus data?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <!-- ===== POPUP FILTER ===== -->
    <div class="filter-modal" id="filterModal">
        <div class="filter-content">
            <h3>Filter Transaksi</h3>

            <form method="GET">
                <label>Pilih Bulan</label>
                <select name="bulan" class="filter-input">
                    <option value="">Semua Bulan</option>
                    <?php
                    $namaBulan = [
                        1 => "Januari",
                        2 => "Februari",
                        3 => "Maret",
                        4 => "April",
                        5 => "Mei",
                        6 => "Juni",
                        7 => "Juli",
                        8 => "Agustus",
                        9 => "September",
                        10 => "Oktober",
                        11 => "November",
                        12 => "Desember"
                    ];
                    for ($i = 1; $i <= 12; $i++):
                        ?>
                        <option value="<?= $i ?>"><?= $namaBulan[$i] ?></option>
                    <?php endfor; ?>
                </select>

                <label>Pilih Tahun</label>
                <select name="tahun" class="filter-input">
                    <option value="">Semua Tahun</option>
                    <?php
                    $tahunSekarang = date("Y");
                    for ($t = $tahunSekarang; $t >= 2020; $t--): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endfor; ?>
                </select>

                <div class="filter-btn-group">
                    <button type="submit" class="apply-btn">Terapkan</button>
                    <button type="button" class="close-btn" id="closeFilter">Batal</button>
                </div>
            </form>
        </div>
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
        // ===== Active Link =====
        const links = document.querySelectorAll(".link li a");

        // ============ Jika ada data aktif tersimpan, apply ============
        const activePage = localStorage.getItem("activeLink");
        if (activePage) {
            links.forEach(link => {
                if (link.getAttribute("href") === activePage) {
                    link.classList.add("active");
                }
            });
        }

        // ============ Saat menu diklik ============
        links.forEach(link => {
            link.addEventListener("click", () => {
                // hapus active di semua
                links.forEach(l => l.classList.remove("active"));

                // kasih active ke yang diklik
                link.classList.add("active");

                // simpan href yang aktif ke localStorage
                localStorage.setItem("activeLink", link.getAttribute("href"));
            });
        });

        // ===== POP UP FILTER =====
        document.getElementById("openFilter").onclick = function () {
            document.getElementById("filterModal").style.display = "flex";
        };

        document.getElementById("closeFilter").onclick = function () {
            document.getElementById("filterModal").style.display = "none";
        };
    </script>
</body>

</html>