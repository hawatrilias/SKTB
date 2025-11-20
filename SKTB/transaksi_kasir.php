<?php
include 'config.php';
session_start();

// Hanya role 'admin' ATAU 'kasir' yang diizinkan
$allowed_roles = ['admin', 'kasir'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
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
    <link rel="stylesheet" href="kasir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container">

        <div class="navbar">
            <div class="judul">
                <h2>Floristy <span>Muse</span></h2>
            </div>
            <ul class="link">
                <li><a href="kasir.php"><i class="fa-solid fa-chart-line"></i>Dashboard</a></li>
                <li><a href="transaksi_kasir.php"><i class="fa-solid fa-cash-register"></i>Transaksi</a></li>
                <li><a href="produk_kasir.php"><i class="fa-solid fa-box"></i>Produk</a></li>
                <li><a href="signout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
        <!-- Konten -->
        <main class="dashboard-container">
            <div class="tb-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3>Transaksi</h3>
                    <div style="margin-left: 30px; display:flex; gap: 10px;">
                        <button class="filter-btn" id="openFilter"><i class="fas fa-filter"></i> Filter</button>
                        <a href="tambah_transaksi_kasir.php" class="add-btn">Tambah Transaksi</a>
                    </div>
                </div>
                <table class="trans-table">
                    <thead>
                        <tr>
                            <th width="120">Tanggal</th>
                            <th width="80">Nama Pelanggan</th>
                            <th width="120">Produk</th>
                            <th width="80">Kategori</th>
                            <th width="80">Jumlah</th>
                            <th width="80">Total Bayar</th>
                            <th width="80">Metode</th>
                            <th width="80">Aksi</th>
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
                                    <a href="edit_transaksi_kasir.php?id=<?= $row['id_transaksi']; ?>" class="edit"><i
                                            class="fas fa-edit"></i></a>
                                    <a href="hapus_transaksi_kasir.php?id=<?= $row['id_transaksi']; ?>" class="delete"
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
                    <li><a href="kasir.php">Dashboard</a></li>
                    <li><a href="transaksi_kasir.php">Transaksi</a></li>
                    <li><a href="produk_kasir.php">Produk</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Kontak</h4>
                <p>📍 Surakarta</p>
                <p>📞 0812-3456-7890</p>
                <p>✉ floristymuse@gmail.com</p>
            </div>

            <div class="footer-social">
                <h4>Ikuti Kami</h4>
                <a href="#">Instagram</a><br>
                <a href="#">TikTok</a><br>
                <a href="#">WhatsApp</a>
            </div>
        </div>

        <div class="footer-bottom" align="center">
            <p>© 2025 Floristy Muse · All Rights Reserved.</p>
        </div>
    </footer>
    <script>

        // ===== Active Link =====
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