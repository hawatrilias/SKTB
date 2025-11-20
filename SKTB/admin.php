<?php
// ================== Koneksi ================== //
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';


// ================== Total Keseluruhan ================== //
// Hitung total pemasukan
$sqlPemasukan = "SELECT SUM(nominal) AS total FROM pemasukan";
$totalPemasukan = mysqli_fetch_assoc(mysqli_query($conn, $sqlPemasukan))['total'] ?? 0;

// Hitung total pengeluaran
$sqlPengeluaran = "SELECT SUM(nominal) AS total FROM pengeluaran";
$totalPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, $sqlPengeluaran))['total'] ?? 0;

// Hitung saldo kas
$saldoKas = $totalPemasukan - $totalPengeluaran;

// ================== Persentase Bulanan ================== //
$bulanSekarang = date('m');
$tahunSekarang = date('Y');

// Bulan lalu
$bulanLalu = $bulanSekarang - 1;
$tahunLalu = $tahunSekarang;
if ($bulanLalu == 0) {
    $bulanLalu = 12;
    $tahunLalu--;
}

// ===== Pemasukan Bulan Ini & Bulan Lalu =====
$pemasukanNow = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(nominal) AS total FROM pemasukan WHERE MONTH(tanggal)=$bulanSekarang AND YEAR(tanggal)=$tahunSekarang"
))['total'] ?? 0;

$pemasukanPrev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(nominal) AS total FROM pemasukan WHERE MONTH(tanggal)=$bulanLalu AND YEAR(tanggal)=$tahunLalu"
))['total'] ?? 0;

// Hitung persentase pemasukan dengan aman
if ($pemasukanPrev > 0) {
    $persenPemasukan = (($pemasukanNow - $pemasukanPrev) / $pemasukanPrev) * 100;
} elseif ($pemasukanPrev == 0 && $pemasukanNow > 0) {
    $persenPemasukan = null; // tampilkan "-" jika bulan lalu 0
} else {
    $persenPemasukan = 0;
}

// ===== Pengeluaran Bulan Ini & Bulan Lalu =====
$pengeluaranNow = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(nominal) AS total FROM pengeluaran WHERE MONTH(tanggal)=$bulanSekarang AND YEAR(tanggal)=$tahunSekarang"
))['total'] ?? 0;

$pengeluaranPrev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(nominal) AS total FROM pengeluaran WHERE MONTH(tanggal)=$bulanLalu AND YEAR(tanggal)=$tahunLalu"
))['total'] ?? 0;

// Hitung persentase pengeluaran dengan aman
if ($pengeluaranPrev > 0) {
    $persenPengeluaran = (($pengeluaranNow - $pengeluaranPrev) / $pengeluaranPrev) * 100;
} elseif ($pengeluaranPrev == 0 && $pengeluaranNow > 0) {
    $persenPengeluaran = null;
} else {
    $persenPengeluaran = 0;
}

// ===== Saldo Bulan Ini & Bulan Lalu =====
$saldoNow = $pemasukanNow - $pengeluaranNow;
$saldoPrev = $pemasukanPrev - $pengeluaranPrev;

// Hitung persentase saldo dengan aman
if ($saldoPrev != 0) {
    $persenSaldo = (($saldoNow - $saldoPrev) / abs($saldoPrev)) * 100;
} elseif ($saldoPrev == 0 && $saldoNow != 0) {
    $persenSaldo = null; // tampilkan "-" jika bulan lalu saldo 0
} else {
    $persenSaldo = 0;
}

// ================== Transaksi Terakhir ================== //
$sqlTransaksi = "SELECT tanggal, keterangan AS barang, nominal, kategori
                 FROM pengeluaran 
                 ORDER BY tanggal DESC 
                 LIMIT 5";
$resultTransaksi = mysqli_query($conn, $sqlTransaksi);

// ================== Data Chart Bulanan ================== //
$sqlChart = "
    SELECT 
        MONTH(tanggal) AS bulan, 
        YEAR(tanggal) AS tahun, 
        SUM(CASE WHEN tipe='pemasukan' THEN nominal ELSE 0 END) AS total_pemasukan,
        SUM(CASE WHEN tipe='pengeluaran' THEN nominal ELSE 0 END) AS total_pengeluaran
    FROM (
        SELECT tanggal, nominal, 'pemasukan' AS tipe FROM pemasukan
        UNION ALL
        SELECT tanggal, nominal, 'pengeluaran' AS tipe FROM pengeluaran
    ) AS semua_transaksi
    GROUP BY tahun, bulan
    ORDER BY tahun, bulan
";

$resultChart = mysqli_query($conn, $sqlChart);

$bulanLabels = [];
$pemasukanData = [];
$pengeluaranData = [];

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

while ($row = mysqli_fetch_assoc($resultChart)) {
    $bulanLabels[] = $namaBulan[(int)$row['bulan']];
    $pemasukanData[] = (float)$row['total_pemasukan'];
    $pengeluaranData[] = (float)$row['total_pengeluaran'];
}

// ================== Data Donut Chart (Total Pemesanan) ================== //
$sqlDonut = "
    SELECT kategori, COUNT(*) AS total 
    FROM transaksi
    GROUP BY kategori
";
$resultDonut = mysqli_query($conn, $sqlDonut);

$kategoriLabels = [];
$kategoriData = [];

while ($row = mysqli_fetch_assoc($resultDonut)) {
    $kategoriLabels[] = $row['kategori'];
    $kategoriData[] = (int)$row['total'];
}

// Ambil semua data transaksi
$sql = "SELECT * FROM transaksi ORDER BY tanggal DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floristy Muse - Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-neutral {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
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

        <!-- Dashboard -->
        <main class="dashboard-container">
            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <h4>Pemasukan</h4>
                    <p>Rp <?= number_format($totalPemasukan, 2, ',', '.'); ?></p>
                    <?php if ($persenPemasukan > 0): ?>
                        <span class="stat-up">↑ <?= number_format($persenPemasukan, 2); ?>% vs bulan lalu</span>
                    <?php elseif ($persenPemasukan < 0): ?>
                        <span class="stat-down">↓ <?= number_format(abs($persenPemasukan), 2); ?>% vs bulan lalu</span>
                    <?php else: ?>
                        <span class="stat-neutral">→ <?= $persenPemasukan === null ? '-' : '0'; ?>% vs bulan lalu</span>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h4>Pengeluaran</h4>
                    <p>Rp <?= number_format($totalPengeluaran, 2, ',', '.'); ?></p>
                    <?php if ($persenPengeluaran > 0): ?>
                        <span class="stat-up">↑ <?= number_format($persenPengeluaran, 2); ?>% vs bulan lalu</span>
                    <?php elseif ($persenPengeluaran < 0): ?>
                        <span class="stat-down">↓ <?= number_format(abs($persenPengeluaran), 2); ?>% vs bulan lalu</span>
                    <?php else: ?>
                        <span class="stat-neutral">→ <?= $persenPengeluaran === null ? '-' : '0'; ?>% vs bulan lalu</span>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h4>Saldo Kas</h4>
                    <p>Rp <?= number_format($saldoKas, 2, ',', '.'); ?></p>
                    <?php if ($persenSaldo > 0): ?>
                        <span class="stat-up">↑ <?= number_format($persenSaldo, 2); ?>% vs bulan lalu</span>
                    <?php elseif ($persenSaldo < 0): ?>
                        <span class="stat-down">↓ <?= number_format(abs($persenSaldo), 2); ?>% vs bulan lalu</span>
                    <?php else: ?>
                        <span class="stat-neutral">→ <?= $persenSaldo === null ? '-' : '0'; ?>% vs bulan lalu</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <canvas id="chartKeuangan"></canvas>
            </div>

            <!-- Bottom Section -->
            <div class="bottom-section">
                <!-- Tabel Transaksi -->
                <div class="tb-container">
                    <h4>Transaksi Terakhir</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>NAMA PELANGGAN</th>
                                <th>PRODUK</th>
                                <th>KATEGORI</th>
                                <th>JUMLAH</th>
                                <th>TOTAL BAYAR</th>
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
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Donut Chart -->
                <div class="donut-container">
                    <h4>Total Pemesanan</h4>
                    <canvas id="chartDonut"></canvas>
                </div>
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

        // ===== Bar Chart =====
        const ctx = document.getElementById('chartKeuangan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabels); ?>,
                datasets: [
                    { label: 'Pemasukan', data: <?= json_encode($pemasukanData); ?>, backgroundColor: '#ef88ad' },
                    { label: 'Pengeluaran', data: <?= json_encode($pengeluaranData); ?>, backgroundColor: '#670d2f' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });

        // ===== Donut Chart =====
        const ctxDonut = document.getElementById('chartDonut').getContext('2d');
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($kategoriLabels); ?>,
                datasets: [{ data: <?= json_encode($kategoriData); ?>, backgroundColor: ['#ef88ad', '#a84c66', '#670d2f', '#ffb6c1', '#ff69b4'] }]
            },
            options: { responsive: true, cutout: '70%', radius: 130, maintainAspectRatio: false }
        });
    </script>
</body>
</html>
