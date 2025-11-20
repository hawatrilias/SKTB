<?php
// Pastikan file config.php dan session sudah terinisialisasi
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan untuk mengakses file ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); 
    exit();
}

require_once 'config.php';

// ================== FILTER DARI URL ================== //
$bulan  = $_GET['bulan'] ?? '';
$tahun  = $_GET['tahun'] ?? '';

// ================== BANGUN FILTER QUERY SQL ================== //
$filterQuery = "WHERE 1=1";
if ($bulan !== '') $filterQuery .= " AND MONTH(tanggal) = '" . mysqli_real_escape_string($conn, $bulan) . "'";
if ($tahun !== '') $filterQuery .= " AND YEAR(tanggal) = '" . mysqli_real_escape_string($conn, $tahun) . "'";


// ================== 1. HITUNG DATA RINGKASAN (SUMMARY) ================== //

// Query untuk Pemasukan
$sqlPemasukan = "SELECT SUM(nominal) AS total FROM pemasukan WHERE 1=1";
if ($bulan !== '') $sqlPemasukan .= " AND MONTH(tanggal) = '" . mysqli_real_escape_string($conn, $bulan) . "'";
if ($tahun !== '') $sqlPemasukan .= " AND YEAR(tanggal) = '" . mysqli_real_escape_string($conn, $tahun) . "'";
$totalPemasukan = mysqli_fetch_assoc(mysqli_query($conn, $sqlPemasukan))['total'] ?? 0;

// Query untuk Pengeluaran
$sqlPengeluaran = "SELECT SUM(nominal) AS total FROM pengeluaran WHERE 1=1";
if ($bulan !== '') $sqlPengeluaran .= " AND MONTH(tanggal) = '" . mysqli_real_escape_string($conn, $bulan) . "'";
if ($tahun !== '') $sqlPengeluaran .= " AND YEAR(tanggal) = '" . mysqli_real_escape_string($conn, $tahun) . "'";
$totalPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, $sqlPengeluaran))['total'] ?? 0;

$saldo = $totalPemasukan - $totalPengeluaran;


// ================== 2. QUERY DATA DETAIL TRANSAKSI ================== //
$sqlLaporan = "
    SELECT 
        tanggal,
        keterangan,
        CASE WHEN tipe='pemasukan' THEN 'Pemasukan' ELSE 'Pengeluaran' END AS jenis,
        nominal
    FROM (
        SELECT tanggal, keterangan, nominal, 'pemasukan' AS tipe FROM pemasukan
        UNION ALL
        SELECT tanggal, keterangan, nominal, 'pengeluaran' AS tipe FROM pengeluaran
    ) AS semua
    $filterQuery
    ORDER BY tanggal DESC
";
$resultLaporan = mysqli_query($conn, $sqlLaporan);


// ================== 3. PENGATURAN HEADER HTTP UNTUK DOWNLOAD CSV ================== //
$filename = "laporan_keuangan";
if ($bulan && $tahun) {
    $filename .= "_Bulan_" . $bulan . "_Tahun_" . $tahun;
} elseif ($tahun) {
    $filename .= "_Tahun_" . $tahun;
} elseif ($bulan) {
    $filename .= "_Bulan_" . $bulan;
}
$filename .= "_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));


// ================== 4. TULIS DATA RINGKASAN (SUMMARY) ================== //

// Tulis Judul Laporan dan Periode
$periode = "Semua Periode";
if ($bulan && $tahun) {
    $periode = "Bulan $bulan Tahun $tahun";
} elseif ($tahun) {
    $periode = "Tahun $tahun";
} elseif ($bulan) {
    $periode = "Bulan $bulan";
}

fputcsv($output, ["LAPORAN KEUANGAN FLORISTY MUSE"], ';');
fputcsv($output, ["Periode Laporan:", $periode], ';');
fputcsv($output, [], ';'); // Baris kosong

// Tulis Ringkasan
fputcsv($output, ["RINGKASAN KEUANGAN"], ';');
fputcsv($output, ["Total Pemasukan:", $totalPemasukan], ';');
fputcsv($output, ["Total Pengeluaran:", $totalPengeluaran], ';');
fputcsv($output, ["Saldo Bersih:", $saldo], ';');
fputcsv($output, [], ';'); // Baris kosong sebelum detail


// ================== 5. TULIS DATA DETAIL TRANSAKSI ================== //

fputcsv($output, ["DETAIL TRANSAKSI"], ';');
// Baris Header Kolom
$header = ['Tanggal', 'Keterangan', 'Jenis Transaksi', 'Nominal'];
fputcsv($output, $header, ';');

// Loop data dan tulis ke file CSV
while ($row = mysqli_fetch_assoc($resultLaporan)) {
    $data_row = [
        date('d/m/Y', strtotime($row['tanggal'])),
        $row['keterangan'],
        $row['jenis'],
        (float)$row['nominal'] 
    ];
    
    fputcsv($output, $data_row, ';');
}

fclose($output);
exit;
?>