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
$errMsg = "";

// Ambil data pengguna
$sql = "SELECT * FROM pengguna WHERE id_pengguna='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    // Validasi input
    if (empty($name)) {
        $errMsg = "Username wajib diisi!";
    } elseif (empty($email)) {
        $errMsg = "Email wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errMsg = "Format email tidak valid!";
    } else {
        // Cek apakah name sudah ada (kecuali name lama)
        $cekUsername = "SELECT * FROM pengguna WHERE name = '$name' AND id_pengguna != '$id'";
        $resultCek = mysqli_query($conn, $cekUsername);
        if (mysqli_num_rows($resultCek) > 0) {
            $errMsg = "Username sudah digunakan!";
        } else {
            // Jika password diisi, update password. Jika tidak, biarkan password lama
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sqlUpdate = "UPDATE pengguna SET 
                              name='$name',
                              password='$passwordHash',
                              email='$email',
                              role='$role'
                            WHERE id_pengguna='$id'";
            } else {
                $sqlUpdate = "UPDATE pengguna SET 
                              name='$name',
                              email='$email',
                              role='$role'
                            WHERE id_pengguna='$id'";
            }
            
            if (mysqli_query($conn, $sqlUpdate)) {
                header("Location: pengguna.php");
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
    <title>Edit Pengguna - Floristy Muse</title>
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
        <!-- Main Content -->
        <main class="dashboard-container">
            <div class="form-container">
                <h3>Edit Pengguna</h3>
                
                <!-- Tampilkan pesan error jika ada -->
                <?php if (!empty($errMsg)): ?>
                    <div class="error-message"><?= $errMsg ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="name" value="<?= isset($data['name']) ? $data['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password <small>(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= isset($data['email']) ? $data['email'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" <?= (isset($data['role']) && $data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="kasir" <?= (isset($data['role']) && $data['role'] == 'kasir') ? 'selected' : ''; ?>>Kasir</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="add-btn">Update Pengguna</button>
                        <a href="pengguna.php" class="add-btn" style="background:gray;">Batal</a>
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
    </script>
</body>


</html>