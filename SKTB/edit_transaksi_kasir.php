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

$id = $_GET['id'];
$sql = "SELECT * FROM transaksi WHERE id_transaksi='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $produk = $_POST['produk'];
    $kategori = $_POST['kategori']; // ✅ ambil kategori
    $jumlah = $_POST['jumlah'];
    $total = $_POST['total'];
    $metode = $_POST['metode'];

    $sqlUpdate = "UPDATE transaksi SET 
                  tanggal='$tanggal',
                  nama_pelanggan='$nama_pelanggan',
                  produk='$produk',
                  kategori='$kategori',
                  jumlah='$jumlah',
                  total='$total',
                  metode='$metode'
                WHERE id_transaksi='$id'";

    if (mysqli_query($conn, $sqlUpdate)) {
        header("Location: transaksi_kasir.php");
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
    <title>Edit Transaksi</title>
    <link rel="stylesheet" href="kasir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <aside class="navbar">
            <div class="judul">
                <h2>Floristy <span>Muse</span></h2>
            </div>
            <ul class="link">
                <li><a href="kasir.php"><i class="fa-solid fa-chart-line"></i>Dashboard</a></li>
                <li><a href="transaksi_kasir.php"><i class="fa-solid fa-cash-register"></i>Transaksi</a></li>
                <li><a href="produk_kasir.php"><i class="fa-solid fa-box"></i>Produk</a></li>
                <li><a href="signout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="dashboard-container">
            <div class="form-container">
                <h3>Edit Transaksi</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d', strtotime($data['tanggal'])); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" value="<?= $data['nama_pelanggan']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Produk</label>
                        <input type="text" name="produk" value="<?= $data['produk']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="Pipe Cleaner" <?= $data['kategori'] == 'Pipe Cleaner' ? 'selected' : ''; ?>>Pipe
                                Cleaner</option>
                            <option value="Fresh Flower" <?= $data['kategori'] == 'Fresh Flower' ? 'selected' : ''; ?>>
                                Fresh Flower</option>
                            <option value="Fake Flower" <?= $data['kategori'] == 'Fake Flower' ? 'selected' : ''; ?>>Fake
                                Flower</option>
                            <option value="Lainnya" <?= $data['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" value="<?= $data['jumlah']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Total Bayar (Rp)</label>
                        <input type="number" name="total" value="<?= $data['total']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode" required>
                            <option value="Cash" <?= $data['metode'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Transfer" <?= $data['metode'] == 'Transfer' ? 'selected' : ''; ?>>Transfer
                            </option>
                            <option value="QRIS" <?= $data['metode'] == 'QRIS' ? 'selected' : ''; ?>>QRIS</option>
                        </select>
                    </div>
                    <button type="submit" class="add-btn">Update</button>
                    <a href="transaksi_kasir.php" class="add-btn" style="background:gray;">Batal</a>
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