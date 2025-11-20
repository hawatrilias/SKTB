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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // Validasi input
    if (empty($nama_produk)) {
        $errMsg = "Nama produk wajib diisi!";
    } elseif (empty($kategori)) {
        $errMsg = "Kategori wajib dipilih!";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $errMsg = "Harga harus berupa angka positif!";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $errMsg = "Stok harus berupa angka tidak negatif!";
    } else {
        // Proses upload gambar
        $gambar = "";
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['gambar']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validasi tipe file
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extension, $allowedTypes)) {
                $errMsg = "Hanya menerima file gambar (JPG, JPEG, PNG, GIF)!";
            } else {
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

        // Jika tidak ada error, simpan ke database
        if (empty($errMsg)) {
            $sql = "INSERT INTO produk (nama_produk, kategori, harga, stok, gambar)
                    VALUES ('$nama_produk','$kategori','$harga','$stok','$gambar')";

            if (mysqli_query($conn, $sql)) {
                header("Location: produk.php");
                exit;
            } else {
                $errMsg = "Error SQL: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Tambahan CSS untuk form produk */
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 12px 15px;
            background: var(--light-color);
            border: 1px dashed var(--border-color);
            border-radius: 6px;
            text-align: center;
            color: var(--text-light);
            transition: var(--transition);
        }

        .file-upload:hover .file-upload-label {
            background: #f0f0f0;
            border-color: var(--primary-color);
        }

        .preview-container {
            margin-top: 10px;
            text-align: center;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
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
                <h3>Tambah Produk</h3>

                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($errMsg)): ?>
                    <div class="error-message"><?= $errMsg ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Pipe Cleaner">Pipe Cleaner</option>
                            <option value="Fresh Flower">Fresh Flower</option>
                            <option value="Fake Flower">Fake Flower</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" min="0" step="100" required>
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Gambar Produk</label>
                        <div class="file-upload">
                            <input type="file" name="gambar" id="gambar" accept="image/*">
                            <label for="gambar" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> Klik untuk upload gambar
                            </label>
                        </div>
                        <div class="preview-container" id="previewContainer" style="display: none;">
                            <img id="imagePreview" class="preview-image" alt="Preview">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="add-btn">Simpan Produk</button>
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

        // Preview gambar sebelum upload
        document.getElementById('gambar').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }

                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    </script>
</body>

</html>