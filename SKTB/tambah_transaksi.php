<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

$errMsg = "";

// Ambil data produk dari database
$sqlProduk = "SELECT * FROM produk ORDER BY nama_produk ASC";
$resultProduk = mysqli_query($conn, $sqlProduk);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $produk = $_POST['produk'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $total = $_POST['total'];
    $metode = $_POST['metode'];

    // Validasi kategori tidak boleh kosong
    if (empty($kategori)) {
        $errMsg = "Kategori wajib dipilih!";
    } else {
        $sql = "INSERT INTO transaksi (tanggal, nama_pelanggan, produk, kategori, jumlah, total, metode)
                VALUES ('$tanggal','$nama_pelanggan','$produk','$kategori','$jumlah','$total','$metode')";
        if (mysqli_query($conn, $sql)) {
            header("Location: transaksi.php");
            exit;
        } else {
            $errMsg = "Error SQL: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Tambahan CSS untuk form transaksi */
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions .add-btn {
            flex: 1;
            text-align: center;
        }

        /* Style untuk produk info */
        .produk-info {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .produk-info-item {
            flex: 1;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .produk-info-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .produk-info-value {
            font-weight: 600;
            color: var(--dark-color);
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

        <main class="dashboard-container">
            <div class="form-container">
                <h3>Tambah Transaksi</h3>

                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($errMsg)): ?>
                    <div class="error-message"><?= $errMsg ?></div>
                <?php endif; ?>

                <form method="POST" id="transaksiForm">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" required>
                    </div>

                    <div class="form-group">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" required>
                    </div>

                    <div class="form-group">
                        <label>Produk</label>
                        <select name="produk" id="produkSelect" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php
                            // Reset pointer result
                            mysqli_data_seek($resultProduk, 0);
                            while ($row = mysqli_fetch_assoc($resultProduk)):
                                ?>
                                <option value="<?= $row['nama_produk']; ?>" data-kategori="<?= $row['kategori']; ?>"
                                    data-harga="<?= $row['harga']; ?>">
                                    <?= $row['nama_produk']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" id="kategoriInput" readonly required>
                    </div>

                    <div class="form-group">
                        <label>Harga Satuan (Rp)</label>
                        <input type="number" name="harga_satuan" id="hargaInput" readonly required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" id="jumlahInput" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Total Bayar (Rp)</label>
                        <input type="number" name="total" id="totalInput" readonly required>
                    </div>

                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="metode" required>
                            <option value="">-- Pilih --</option>
                            <option value="Cash">Cash</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="add-btn">Simpan</button>
                        <a href="transaksi.php" class="add-btn" style="background:gray;">Batal</a>
                    </div>
                </form>

                <!-- Info Produk yang Dipilih -->
                <div id="produkInfoContainer" style="display: none; margin-top: 20px;">
                    <h4 style="margin-bottom: 10px; color: var(--primary-color);">Informasi Produk</h4>
                    <div class="produk-info">
                        <div class="produk-info-item">
                            <div class="produk-info-label">Kategori</div>
                            <div class="produk-info-value" id="infoKategori">-</div>
                        </div>
                        <div class="produk-info-item">
                            <div class="produk-info-label">Harga Satuan</div>
                            <div class="produk-info-value" id="infoHarga">-</div>
                        </div>
                        <div class="produk-info-item">
                            <div class="produk-info-label">Subtotal</div>
                            <div class="produk-info-value" id="infoSubtotal">-</div>
                        </div>
                    </div>
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

        // Ambil elemen-elemen form
        const produkSelect = document.getElementById('produkSelect');
        const kategoriInput = document.getElementById('kategoriInput');
        const hargaInput = document.getElementById('hargaInput');
        const jumlahInput = document.getElementById('jumlahInput');
        const totalInput = document.getElementById('totalInput');
        const produkInfoContainer = document.getElementById('produkInfoContainer');
        const infoKategori = document.getElementById('infoKategori');
        const infoHarga = document.getElementById('infoHarga');
        const infoSubtotal = document.getElementById('infoSubtotal');

        // Fungsi untuk menghitung total
        function hitungTotal() {
            const harga = parseFloat(hargaInput.value) || 0;
            const jumlah = parseInt(jumlahInput.value) || 0;
            const total = harga * jumlah;

            totalInput.value = total;
            infoSubtotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Event listener saat produk dipilih
        produkSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                // Ambil data dari atribut data
                const kategori = selectedOption.getAttribute('data-kategori');
                const harga = selectedOption.getAttribute('data-harga');

                // Isi form
                kategoriInput.value = kategori;
                hargaInput.value = harga;

                // Tampilkan info produk
                produkInfoContainer.style.display = 'block';
                infoKategori.textContent = kategori;
                infoHarga.textContent = 'Rp ' + parseInt(harga).toLocaleString('id-ID');

                // Hitung total
                hitungTotal();
            } else {
                // Reset form jika tidak ada produk yang dipilih
                kategoriInput.value = '';
                hargaInput.value = '';
                totalInput.value = '';
                produkInfoContainer.style.display = 'none';
            }
        });

        // Event listener saat jumlah berubah
        jumlahInput.addEventListener('input', hitungTotal);

        // Set tanggal hari ini sebagai default
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="tanggal"]').value = today;
        });
    </script>
</body>

</html>