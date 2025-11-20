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

// ================== Data Pengguna ================== //
$sqlPengguna = "SELECT id_pengguna, name, email, role FROM pengguna ORDER BY id_pengguna DESC";
$resultPengguna = mysqli_query($conn, $sqlPengguna);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floristy Muse - Pengguna</title>
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

        <!-- Konten Utama -->
        <main class="dashboard-container">
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3>Daftar Pengguna</h3>
                    <button class="add-btn" onclick="window.location.href='tambah_pengguna.php'">
                        Tambah Pengguna
                    </button>
                </div>

                <?php if (mysqli_num_rows($resultPengguna) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>NAMA</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($resultPengguna)): ?>
                                <tr>
                                    <td><?= $row['name']; ?></td>
                                    <td><?= $row['email']; ?></td>
                                    <td><?= ucfirst($row['role']); ?></td>
                                    <td class="action-btn">
                                        <a href="edit_pengguna.php?id=<?= $row['id_pengguna']; ?>" class="edit"><i
                                                class="fas fa-edit"></i></a>
                                        <a href="hapus_pengguna.php?id=<?= $row['id_pengguna']; ?>" class="delete"
                                            onclick="return confirm('Yakin hapus data?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-table">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>Belum ada data pengguna</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
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

        function confirmDelete(userId) {
            if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
                window.location.href = 'hapus_pengguna.php?id=' + userId;
            }
        }
    </script>
</body>

</html>