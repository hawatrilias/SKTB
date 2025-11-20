<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';
$id = $_GET['id'];
$sql = "SELECT * FROM produk WHERE id_produk='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input untuk keamanan
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    
    // Handle gambar lama
    $gambar = $data['gambar'];
    $errMsg = ""; // Inisialisasi variabel pesan error
    
    // Cek apakah ada gambar baru yang diupload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($_FILES['gambar']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validasi tipe file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedTypes)) {
            $errMsg = "Hanya menerima file gambar (JPG, JPEG, PNG, GIF)!";
        } else {
            // Hapus gambar lama jika ada
            if (!empty($data['gambar']) && file_exists('pict/' . $data['gambar'])) {
                unlink('pict/' . $data['gambar']);
            }
            
            // Buat nama file unik
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = 'pict/' . $fileName;
            
            // Pindahkan file ke folder tujuan
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                $gambar = $fileName;
            } else {
                $errMsg = "Gagal mengupload gambar!";
            }
        }
    }
    
    // Jika tidak ada error, update database
    if (empty($errMsg)) {
        $sqlUpdate = "UPDATE produk SET 
                      nama_produk='$nama_produk',
                      kategori='$kategori',
                      harga='$harga',
                      stok='$stok',
                      gambar='$gambar'
                    WHERE id_produk='$id'";
        
        if (mysqli_query($conn, $sqlUpdate)) {
            header("Location: produk.php");
            exit;
        } else {
            $errMsg = "Gagal memperbarui data: " . mysqli_error($conn);
            
            // Jika update gagal, ambil ulang data dari DB untuk mengisi form
            $sql = "SELECT * FROM produk WHERE id_produk='$id'";
            $result = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($result);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --border-color: #ccc; 
        }
        
        .current-image {
            margin-top: 10px;
            text-align: center; 
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            object-fit: cover; 
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .form-actions .add-btn {
            flex: 1;
            text-align: center;
        }

        .error-message {
            background-color: #fdd;
            color: #a00;
            padding: 10px;
            border: 1px solid #f99;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .file-upload-label {
            display: block;
            padding: 10px 15px;
            background: #f7f7f7;
            border: 2px dashed var(--border-color);
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            color: #666;
        }
        .file-upload input[type="file"] {
             display: none; 
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
        <div class="form-container">
            <h3>Edit Produk</h3>
            
            <?php if (!empty($errMsg)): ?>
                <div class="error-message"><?= htmlspecialchars($errMsg) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" value="<?= htmlspecialchars($data['nama_produk']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php $current_kategori = $data['kategori']; ?>
                        <option value="Pipe Cleaner" <?= $current_kategori == 'Pipe Cleaner' ? 'selected' : ''; ?>>Pipe Cleaner</option>
                        <option value="Fresh Flower" <?= $current_kategori == 'Fresh Flower' ? 'selected' : ''; ?>>Fresh Flower</option>
                        <option value="Fake Flower" <?= $current_kategori == 'Fake Flower' ? 'selected' : ''; ?>>Fake Flower</option>
                        <option value="Lainnya" <?= $current_kategori == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" min="0" step="100" value="<?= htmlspecialchars($data['harga']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" min="0" value="<?= htmlspecialchars($data['stok']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Gambar Produk</label>
                    <div class="file-upload">
                        <input type="file" name="gambar" id="gambar" accept="image/*">
                        <label for="gambar" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i> Pilih gambar baru (opsional)
                        </label>
                    </div>
                    
                    <div class="current-image">
                        <p id="image-label">Gambar saat ini:</p>
                        <?php if (!empty($data['gambar'])): ?>
                            <img id="image-preview" src="pict/<?= htmlspecialchars($data['gambar']); ?>" alt="<?= htmlspecialchars($data['nama_produk']); ?>">
                        <?php else: ?>
                            <img id="image-preview" src="#" alt="Pratinjau Gambar Baru" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="add-btn">Update Produk</button>
                    <a href="produk.php" class="add-btn" style="background:gray;">Batal</a>
                </div>
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
    // ===== Active Link (Kode asli) =====
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
    
    // ==========================================================
    // LOGIKA JAVASCRIPT BARU UNTUK PRATINJAU GAMBAR (IMAGE PREVIEW)
    // ==========================================================

    const fileInput = document.getElementById('gambar'); 
    const imagePreview = document.getElementById('image-preview'); 
    const imageLabel = document.getElementById('image-label'); 

    // Simpan sumber gambar lama saat halaman dimuat
    const originalSrc = imagePreview.src;

    fileInput.addEventListener('change', function() {
        // Cek apakah ada file yang dipilih
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Atur src dari <img> dengan Data URL dari gambar baru
                imagePreview.src = e.target.result;
                // Tampilkan gambar jika sebelumnya disembunyikan
                imagePreview.style.display = 'block'; 
                // Ubah label teks
                imageLabel.textContent = 'Gambar baru dipilih:';
            };

            // Baca file yang dipilih
            reader.readAsDataURL(this.files[0]);
        } else {
            // Jika user membatalkan pemilihan, kembalikan ke gambar lama
            imagePreview.src = originalSrc;
            if (originalSrc.includes('pict/')) {
                // Tampilkan kembali gambar lama jika memang ada
                imagePreview.style.display = 'block';
                imageLabel.textContent = 'Gambar saat ini:';
            } else {
                // Sembunyikan jika memang tidak ada gambar lama
                imagePreview.style.display = 'none';
                imageLabel.textContent = 'Gambar saat ini:';
            }
        }
    });

</script>
</body>
</html>