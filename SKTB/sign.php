<?php

session_start();
// Pastikan file config.php sudah ada dan berisi koneksi MySQLi Anda
require_once 'config.php';

// Cek apakah permintaan berasal dari tombol 'signin'
if (isset($_POST['signin'])) {
    // 1. Ambil dan bersihkan input
    $email_or_username = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Gunakan prepared statement untuk mencegah SQL injection
    $stmt = $conn->prepare("SELECT id_pengguna, name, email, password, role FROM pengguna WHERE email = ?");
    
    // Pastikan prepared statement berhasil
    if ($stmt === false) {
        $_SESSION['signin_error'] = 'Kesalahan database saat menyiapkan query.';
        header("Location: signin.php");
        exit();
    }
    
    // Bind parameter dan eksekusi
    $stmt->bind_param("s", $email_or_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 3. Verifikasi Password menggunakan password_verify()
        if (password_verify($password, $user['password'])) {
            // Login Berhasil
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // Harus bernilai 'admin' atau 'kasir'
            
            $stmt->close();
            
            // ====================================================================
            // 4. PERBAIKAN LOGIKA REDIRECT: AKTIFKAN POP-UP UNTUK ADMIN
            // ====================================================================
            if ($user['role'] == 'admin') {
                // SET FLAG POP-UP: Memberi tahu signin.php untuk menampilkan modal
                $_SESSION['show_role_popup'] = true; 
                // Redirect ke halaman login yang sekarang akan menampilkan pop-up
                header("Location: signin.php"); 
            } else { 
                // Role lain (kasir) langsung ke dashboard tanpa pop-up
                header("Location: kasir.php"); 
            }
            exit();

        } else {
            // Password salah
            $_SESSION['signin_error'] = 'Email atau password salah';
        }
    } else {
        // Email tidak ditemukan
        $_SESSION['signin_error'] = 'Email atau password salah'; // Jaga agar pesan tidak terlalu spesifik (keamanan)
    }
    
    $stmt->close();
    
    // 5. Redirect ke halaman form login (jika gagal atau password salah)
    $_SESSION['active_form'] = 'signin';
    header("Location: signin.php");
    exit();
}

// Jika sign.php diakses tanpa POST (tanpa tombol signin), redirect ke halaman login
header("Location: signin.php");
exit();
?>