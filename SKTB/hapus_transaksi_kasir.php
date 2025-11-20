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
$sql = "DELETE FROM transaksi WHERE id_transaksi='$id'";
if (mysqli_query($conn, $sql)) {
  header("Location: transaksi_kasir.php");
  exit;
} else {
  echo "Error: " . mysqli_error($conn);
}
?>
