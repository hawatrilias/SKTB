<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

// Ambil data lama
$id = $_GET['id'];
$sql = "SELECT * FROM pemasukan WHERE id_pemasukan='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $nominal = $_POST['nominal'];
    $sumber = $_POST['sumber'];


    $sqlUpdate = "UPDATE pemasukan SET 
                tanggal='$tanggal',
                keterangan='$keterangan',
                nominal='$nominal',
                sumber='$sumber'
              WHERE id_pemasukan='$id'";

    if (mysqli_query($conn, $sqlUpdate)) {
        header("Location: pemasukan.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Pemasukan</title>
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

        <main class="dashboard-container">
            <div class="form-container">
                <h3>Edit Pemasukan</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= $data['tanggal']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" value="<?= $data['keterangan']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Rp)</label>
                        <input type="number" name="nominal" value="<?= $data['nominal']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sumber</label>
                        <input type="text" name="sumber" value="<?= $data['sumber']; ?>">
                    </div>
                    <button type="submit" class="add-btn">Update</button>
                    <a href="pemasukan.php" class="add-btn" style="background:gray;">Batal</a>
                </form>
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
    </script>
</body>


</html>