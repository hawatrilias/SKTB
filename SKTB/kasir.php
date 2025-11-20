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

// Query total pemasukan
$query_pemasukan = mysqli_query($conn, "SELECT SUM(total) as total FROM transaksi");
$pemasukan = mysqli_fetch_assoc($query_pemasukan)['total'] ?? 0;

// Query total transaksi
$query_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi");
$total_transaksi = mysqli_fetch_assoc($query_transaksi)['total'] ?? 0;

// Query jumlah pelanggan
$query_pelanggan = mysqli_query($conn, "SELECT COUNT(DISTINCT nama_pelanggan) as total FROM transaksi");
$total_pelanggan = mysqli_fetch_assoc($query_pelanggan)['total'] ?? 0;

// Query produk terjual
$query_produk = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM transaksi");
$total_produk = mysqli_fetch_assoc($query_produk)['total'] ?? 0;

// Query transaksi terbaru
$query_terbaru = mysqli_query($conn, "SELECT tanggal, nama_produk, jumlah, total, nama_pelanggan
                                      FROM transaksi 
                                      JOIN produk ON id_produk = id_produk
                                      ORDER BY tanggal DESC
                                      LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Floristy Muse</title>
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

        <div class="dashboard-container">
            <div class="cards">
                <div class="card">
                    <h4>Total Pemasukan</h4>
                    <p>Rp <?= number_format($pemasukan, 2, ',', '.') ?></p>
                </div>
                <div class="card">
                    <h4>Total Transaksi</h4>
                    <p><?= $total_transaksi ?></p>
                </div>
                <div class="card">
                    <h4>Pelanggan</h4>
                    <p><?= $total_pelanggan ?></p>
                </div>
                <div class="card">
                    <h4>Produk Terjual</h4>
                    <p><?= $total_produk ?></p>
                </div>
            </div>

            <div class="table-container">
                <h4>Transaksi Terakhir</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Pemesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query_terbaru)): ?>
                            <tr>
                                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= (int) $row['jumlah'] ?></td>
                                <td>Rp <?= number_format($row['total'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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
    </script>
</body>

</html>