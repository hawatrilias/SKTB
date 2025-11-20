<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

// Ambil ID dari URL
$id = $_GET['id'];

// Hapus data dari database
$sqlDelete = "DELETE FROM pengguna WHERE id_pengguna='$id'";
if (mysqli_query($conn, $sqlDelete)) {
    header("Location: pengguna.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>