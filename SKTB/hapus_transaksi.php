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
$sql = "DELETE FROM transaksi WHERE id_transaksi='$id'";
if (mysqli_query($conn, $sql)) {
  header("Location: transaksi.php");
  exit;
} else {
  echo "Error: " . mysqli_error($conn);
}
?>
