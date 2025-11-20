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

// Ambil data produk untuk mendapatkan nama gambar
$sql = "SELECT gambar FROM produk WHERE id_produk='$id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

// Hapus file gambar jika ada
if (!empty($data['gambar']) && file_exists('pict/' . $data['gambar'])) {
    unlink('pict/' . $data['gambar']);
}

// Hapus data dari database
$sqlDelete = "DELETE FROM produk WHERE id_produk='$id'";
if (mysqli_query($conn, $sqlDelete)) {
    header("Location: produk.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}
?>