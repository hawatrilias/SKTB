<?php
session_start();

// 1. Hapus semua variabel sesi
$_SESSION = array();

// 2. Hancurkan sesi di server
session_destroy();

// 3. Hapus data Active Link dari browser (localStorage)
echo '<script>';
echo 'localStorage.removeItem("activeLink");';
echo '</script>';

// 4. Redirect pengguna kembali ke halaman login (signin.php)
header("Location: signin.php");
exit();
?>