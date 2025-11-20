<?php
include 'config.php';
session_start();

// HANYA role 'admin' yang diizinkan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php'); // Redirect ke halaman login
    exit();
}

require_once 'config.php';

// ================== FILTER DEFAULT ================== //
$filter = $_GET['filter'] ?? 'bulan'; // minggu / bulan / tahun
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

// ================== CARI TANGGAL TERAKHIR DI DATABASE ================== //
$qLatest = mysqli_query($conn, "
    SELECT tanggal FROM (
        SELECT tanggal FROM pemasukan
        UNION ALL
        SELECT tanggal FROM pengeluaran
    ) AS allData
    ORDER BY tanggal DESC
    LIMIT 1
");
$lastDate = mysqli_fetch_assoc($qLatest)['tanggal'] ?? date('Y-m-d');
$lastMonth = date('m', strtotime($lastDate));
$lastYear = date('Y', strtotime($lastDate));

// ================== FILTER QUERY ================== //
$filterQuery = "WHERE 1=1";
if ($bulan !== '')
    $filterQuery .= " AND MONTH(tanggal) = '$bulan'";
if ($tahun !== '')
    $filterQuery .= " AND YEAR(tanggal) = '$tahun'";

// ================== TOTAL KESELURUHAN ================== //
// Total Pemasukan
$sqlPemasukan = "SELECT SUM(nominal) AS total FROM pemasukan $filterQuery";
$totalPemasukan = mysqli_fetch_assoc(mysqli_query($conn, $sqlPemasukan))['total'] ?? 0;

// Total Pengeluaran
$sqlPengeluaran = "SELECT SUM(nominal) AS total FROM pengeluaran $filterQuery";
$totalPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, $sqlPengeluaran))['total'] ?? 0;

$saldoKas = $totalPemasukan - $totalPengeluaran;

// ================== QUERY GRAFIK ================== //
switch ($filter) {
    case 'minggu':
        $whereChart = "WHERE tanggal >= DATE_SUB('$lastDate', INTERVAL 7 DAY)";
        if ($bulan !== '')
            $whereChart .= " AND MONTH(tanggal) = '$bulan'";
        if ($tahun !== '')
            $whereChart .= " AND YEAR(tanggal) = '$tahun'";

        $sqlChart = "
            SELECT 
                DATE(tanggal) AS periode,
                SUM(CASE WHEN tipe='pemasukan' THEN nominal ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN nominal ELSE 0 END) AS total_pengeluaran
            FROM (
                SELECT tanggal, nominal, 'pemasukan' AS tipe FROM pemasukan
                UNION ALL
                SELECT tanggal, nominal, 'pengeluaran' AS tipe FROM pengeluaran
            ) AS semua
            $whereChart
            GROUP BY DATE(tanggal)
            ORDER BY DATE(tanggal)
        ";
        break;

    case 'tahun':
        $chartYear = $tahun ?: $lastYear;
        $whereChart = "WHERE YEAR(tanggal) = '$chartYear'";
        if ($bulan !== '')
            $whereChart .= " AND MONTH(tanggal) = '$bulan'";

        $sqlChart = "
            SELECT 
                MONTH(tanggal) AS periode,
                SUM(CASE WHEN tipe='pemasukan' THEN nominal ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN nominal ELSE 0 END) AS total_pengeluaran
            FROM (
                SELECT tanggal, nominal, 'pemasukan' AS tipe FROM pemasukan
                UNION ALL
                SELECT tanggal, nominal, 'pengeluaran' AS tipe FROM pengeluaran
            ) AS semua
            $whereChart
            GROUP BY MONTH(tanggal)
            ORDER BY MONTH(tanggal)
        ";

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        break;

    default: // BULAN
        $chartMonth = $bulan ?: $lastMonth;
        $chartYear = $tahun ?: $lastYear;

        // gunakan $filterQuery langsung, sudah ada WHERE
        $sqlChart = "
            SELECT 
                DATE(tanggal) AS periode,
                SUM(CASE WHEN tipe='pemasukan' THEN nominal ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN nominal ELSE 0 END) AS total_pengeluaran
            FROM (
                SELECT tanggal, nominal, 'pemasukan' AS tipe FROM pemasukan
                UNION ALL
                SELECT tanggal, nominal, 'pengeluaran' AS tipe FROM pengeluaran
            ) AS semua
            $filterQuery
            GROUP BY DATE(tanggal)
            ORDER BY DATE(tanggal)
        ";
        break;
}

$resultChart = mysqli_query($conn, $sqlChart);

// ================== OLAH DATA UNTUK CHART ================== //
$periodeLabels = [];
$pemasukanData = [];
$pengeluaranData = [];

while ($row = mysqli_fetch_assoc($resultChart)) {
    if ($filter == 'tahun') {
        $periodeLabels[] = $namaBulan[(int) $row['periode']];
    } else {
        $periodeLabels[] = date('d M', strtotime($row['periode']));
    }
    $pemasukanData[] = (float) $row['total_pemasukan'];
    $pengeluaranData[] = (float) $row['total_pengeluaran'];
}

// ================== QUERY TABEL LAPORAN ================== //
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
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floristy Muse - Laporan</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /*====DASHBOARD====*/
        .dashboard-container {
            display: grid;
            grid-template-columns: 8fr 1fr;
        }

        #laporanChart {
            height: 100% !important;
        }


        /*====FILTER==== */
        .filter-container {
            display: flex;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            margin-right: 10px;
            font-size: 14px;
            color: #333;
            outline: none;
        }

        .filter-btn {
            background-color: #670d2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .filter-btn:hover {
            background-color: #8a1540;
        }

        .filter-btn i {
            margin-right: 8px;
        }

        /* Tombol Cetak */
        .print-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }

        .print-btn:hover {
            background-color: #218838;
        }

        .print-btn i {
            margin-right: 8px;
        }

        /* Cards Styles */
        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            margin: 10px;
            flex: 1;
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card h4 {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .card p {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-up {
            color: #28a745;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-down {
            color: #dc3545;
            font-size: 14px;
            font-weight: 500;
        }

        /* Tabs Styles */
        .tabs-container {
            margin-bottom: 30px;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
        }

        .tab-btn {
            padding: 12px 25px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: color 0.3s ease;
        }

        .tab-btn.active {
            color: #670d2f;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #670d2f;
        }

        /* Chart Container */
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            margin: 10px;
            min-height: 200px;
            height: 200px;
            position: relative;
        }

        .chart-container h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Table Container */
        .tb-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .tb-container h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        table {
            width: 60%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f8f9fa;
            color: #670d2f;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Badge Styles */
        .badge-pemasukan {
            display: inline-block;
            padding: 4px 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-pengeluaran {
            display: inline-block;
            padding: 4px 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Bottom Section */
        .bottom-section {
            display: flex;
            gap: 20px;
        }

        .tb-container {
            flex: 2;
        }

        .donut-container {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Popup Cetak */
        .print-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow: auto;
        }

        .print-popup-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .print-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .print-popup-header h3 {
            margin: 0;
            color: #670d2f;
            font-size: 24px;
        }

        .close-popup {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close-popup:hover {
            color: #000;
        }

        .print-options {
            margin-bottom: 25px;
        }

        .print-options h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .print-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .print-option input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .print-option label {
            cursor: pointer;
            font-size: 16px;
        }

        .print-preview {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .print-preview h4 {
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
            color: #670d2f;
        }

        .print-summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .print-summary .card {
            flex: 1;
            margin: 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: none;
        }

        .print-summary .card h4 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #666;
        }

        .print-summary .card p {
            font-size: 18px;
            margin: 0;
            color: #333;
        }

        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .btn-print {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .btn-print:hover {
            background-color: #218838;
        }

        .btn-print i {
            margin-right: 8px;
        }

        /* Style untuk cetak */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #000;
                background: #fff;
            }

            * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            /* Header cetak */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #670d2f;
            }

            .print-header h2 {
                margin: 0;
                font-size: 24px;
                font-weight: bold;
                color: #670d2f;
            }

            .print-header h3 {
                margin: 5px 0 10px;
                font-size: 18px;
                font-weight: normal;
            }

            .print-header p {
                margin: 5px 0;
                font-size: 14px;
            }

            /* Ringkasan cetak */
            .print-summary {
                display: flex !important;
                margin-bottom: 25px;
                gap: 15px;
            }

            .print-summary .card {
                flex: 1;
                margin: 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: none;
                break-inside: avoid;
            }

            .print-summary .card h4 {
                font-size: 14px;
                margin-bottom: 8px;
                color: #666;
            }

            .print-summary .card p {
                font-size: 18px;
                margin: 0;
                color: #333;
            }

            /* Tabel cetak */
            .tb-container {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                margin: 0;
            }

            table {
                width: 100% !important;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th,
            td {
                padding: 8px 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #f8f9fa;
                color: #670d2f;
                font-weight: bold;
                font-size: 12px;
            }

            td {
                font-size: 11px;
            }

            .badge-pemasukan,
            .badge-pengeluaran {
                padding: 2px 8px;
                font-size: 10px;
                border-radius: 10px;
            }

            /* Footer cetak */
            .print-footer {
                display: block !important;
                margin-top: 40px;
                text-align: right;
                font-size: 12px;
            }

            .print-footer p {
                margin: 5px 0;
            }

            .print-footer .signature-line {
                margin-top: 30px;
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
            }

            /* Nomor halaman */
            .page-number:after {
                content: counter(page);
                counter-increment: page;
            }

            /* Menghindari pemotongan baris */
            tr,
            .card {
                page-break-inside: avoid;
            }

            /* Mengatur margin halaman */
            @page {
                margin: 1cm;
                size: A4;
            }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .bottom-section {
                flex-direction: column;
            }

            .donut-container {
                flex: none;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                width: 200px;
            }

            .dashboard-container {
                margin-left: 200px;
                padding: 20px;
            }

            .cards {
                flex-direction: column;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-container {
                margin-top: 15px;
            }

            .print-popup-content {
                width: 95%;
                margin: 20px auto;
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .dashboard-container {
                margin-left: 0;
                padding: 15px;
                display: flex;
            }

            .judul {
                text-align: center;
            }

            .link {
                display: flex;
                flex-wrap: wrap;
                padding: 0 10px;
            }

            .link li {
                flex: 1 0 50%;
                margin-bottom: 5px;
            }

            .link a {
                padding: 10px;
                font-size: 14px;
            }

            .link i {
                margin-right: 5px;
            }

            .print-popup-content {
                width: 100%;
                margin: 10px;
                padding: 15px;
            }

            .print-summary {
                flex-direction: column;
            }
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
        <!-- Laporan -->
        <main class="dashboard-container print-area">
            <!-- Tabel Laporan -->
            <div class="container">
                <div class="judul" style="display: grid; grid-template-columns: 3fr 1fr;">
                    <h2 class="j-lapor" style="color: #670d2f; display: flex; text-align: left; justify-content: left;">
                        Detail Laporan</h2>
                    <div class="filter-container" style="margin-bottom: 1rem;">
                        <button class="filter-btn" id="openFilter"><i class="fas fa-filter"></i> Filter</button>

                        <a id="exportExcelBtn" class="print-btn" style="padding: 8px 24px; text-decoration: none; background-color: #5a6268;">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                        <button class="print-btn" id="showPrintPopup" style="margin-left: 10px;"><i
                                class="fas fa-print"></i> Cetak</button>
                    </div>
                </div>
                <div class="tb-container" style="max-height: 600px ;">
                    <table>
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>KETERANGAN</th>
                                <th>JENIS</th>
                                <th>NOMINAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($resultLaporan)): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= $row['keterangan']; ?></td>
                                    <td>
                                        <?php if ($row['jenis'] == 'Pemasukan'): ?>
                                            <span class="badge-pemasukan">Pemasukan</span>
                                        <?php else: ?>
                                            <span class="badge-pengeluaran">Pengeluaran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rp <?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <section class="no-print">
                <!-- Cards -->
                <div class="card">
                    <h4>Pemasukan</h4>
                    <p>Rp <?= number_format($totalPemasukan, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h4>Pengeluaran</h4>
                    <p>Rp <?= number_format($totalPengeluaran, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h4>Saldo Kas</h4>
                    <p>Rp <?= number_format($saldoKas, 2, ',', '.'); ?></p>
                </div>
                <!-- Chart -->
                <div class="chart-container">
                    <h3>Statistik</h3>
                    <canvas id="laporanChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <!-- Popup Cetak Laporan -->
    <div id="printPopup" class="print-popup no-print">
        <div class="print-popup-content">
            <div class="print-popup-header">
                <h3>Cetak Laporan</h3>
                <span class="close-popup">&times;</span>
            </div>

            <div class="print-options">
                <h4>Opsi Cetak</h4>
                <div class="print-option">
                    <input type="checkbox" id="includeHeader" checked>
                    <label for="includeHeader">Sertakan Header Laporan</label>
                </div>
                <div class="print-option">
                    <input type="checkbox" id="includeSummary" checked>
                    <label for="includeSummary">Sertakan Ringkasan Keuangan</label>
                </div>
                <div class="print-option">
                    <input type="checkbox" id="includeDetails" checked>
                    <label for="includeDetails">Sertakan Detail Transaksi</label>
                </div>
                <div class="print-option">
                    <input type="checkbox" id="includeFooter" checked>
                    <label for="includeFooter">Sertakan Footer & Tanda Tangan</label>
                </div>
            </div>

            <div class="print-preview">
                <h4>Pratinjau Cetak</h4>
                <div class="print-summary">
                    <div class="card">
                        <h4>Total Pemasukan</h4>
                        <p>Rp <?= number_format($totalPemasukan, 2, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h4>Total Pengeluaran</h4>
                        <p>Rp <?= number_format($totalPengeluaran, 2, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h4>Saldo Kas</h4>
                        <p>Rp <?= number_format($saldoKas, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>

            <div class="print-actions">
                <button class="btn-cancel" id="cancelPrint">Batal</button>
                <button class="btn-print" id="confirmPrint">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>

    <!-- Konten Cetak (Tersembunyi) -->
    <div id="printContent" style="display: none;">
        <div class="print-header">
            <h2>LAPORAN KEUANGAN</h2>
            <h3>Floristy Muse</h3>
            <p>Periode: <?php
            if ($filter == 'minggu')
                echo "Mingguan";
            elseif ($filter == 'bulan')
                echo "Bulanan";
            else
                echo "Tahunan";
            ?></p>
            <p>Tanggal Cetak: <?= date('d/m/Y'); ?></p>
        </div>

        <div class="print-summary">
            <div class="card">
                <h4>Total Pemasukan</h4>
                <p>Rp <?= number_format($totalPemasukan, 2, ',', '.'); ?></p>
            </div>
            <div class="card">
                <h4>Total Pengeluaran</h4>
                <p>Rp <?= number_format($totalPengeluaran, 2, ',', '.'); ?></p>
            </div>
            <div class="card">
                <h4>Saldo Kas</h4>
                <p>Rp <?= number_format($saldoKas, 2, ',', '.'); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset pointer result set untuk menampilkan data
                mysqli_data_seek($resultLaporan, 0);
                while ($row = mysqli_fetch_assoc($resultLaporan)):
                    ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td><?= $row['keterangan']; ?></td>
                        <td>
                            <?php if ($row['jenis'] == 'Pemasukan'): ?>
                                <span class="badge-pemasukan">Pemasukan</span>
                            <?php else: ?>
                                <span class="badge-pengeluaran">Pengeluaran</span>
                            <?php endif; ?>
                        </td>
                        <td>Rp <?= number_format($row['nominal'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="print-footer">
            <p>Mengetahui,</p>
            <p class="signature-line"></p>
            <p>Manager</p>
            <p style="margin-top: 20px; text-align: center; font-size: 10px;">
                Halaman <span class="page-number"></span>
            </p>
        </div>
    </div>
    <!-- ===== POPUP FILTER LAPORAN ===== -->
    <div class="filter-modal" id="filterModal">
        <div class="filter-content">
            <h3>Filter Laporan</h3>

            <form method="GET">
                <label>Pilih Bulan</label>
                <select name="bulan" class="filter-input">
                    <option value="">Semua Bulan</option>
                    <?php
                    $namaBulan = [
                        1 => "Januari",
                        2 => "Februari",
                        3 => "Maret",
                        4 => "April",
                        5 => "Mei",
                        6 => "Juni",
                        7 => "Juli",
                        8 => "Agustus",
                        9 => "September",
                        10 => "Oktober",
                        11 => "November",
                        12 => "Desember"
                    ];
                    for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($bulan == $i) ? "selected" : "" ?>>
                            <?= $namaBulan[$i] ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label>Pilih Tahun</label>
                <select name="tahun" class="filter-input">
                    <option value="">Semua Tahun</option>
                    <?php
                    $tahunSekarang = date("Y");
                    for ($t = $tahunSekarang; $t >= 2020; $t--): ?>
                        <option value="<?= $t ?>" <?= ($tahun == $t) ? "selected" : "" ?>>
                            <?= $t ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <div class="filter-btn-group">
                    <button type="submit" class="apply-btn">Terapkan</button>
                    <button type="button" class="close-btn" id="closeFilter">Batal</button>
                </div>
            </form>
        </div>
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

        // ===== Chart Laporan =====
        const ctx = document.getElementById('laporanChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($periodeLabels); ?>,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: <?= json_encode($pemasukanData); ?>,
                        backgroundColor: '#ef88ad'
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?= json_encode($pengeluaranData); ?>,
                        backgroundColor: '#670d2f'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });

        // ==========================================================
        // ===== LOGIKA TOMBOL EXPORT KE EXCEL =====
        // ==========================================================
        const exportBtn = document.getElementById('exportExcelBtn');

        // Ambil nilai filter yang sedang aktif dari PHP dan masukkan ke JS
        // Nilai $bulan dan $tahun diambil dari PHP yang diproses di awal file
        const currentBulan = '<?= htmlspecialchars($bulan); ?>';
        const currentTahun = '<?= htmlspecialchars($tahun); ?>';

        // Bangun URL export yang menunjuk ke export_laporan.php
        let exportUrl = 'export_laporan.php?';

        if (currentBulan) {
            exportUrl += 'bulan=' + currentBulan + '&';
        }
        if (currentTahun) {
            exportUrl += 'tahun=' + currentTahun + '&';
        }

        // Hapus '&' terakhir jika ada, atau pastikan hanya 'export_laporan.php?' jika tidak ada filter
        exportUrl = exportUrl.replace(/&$/, '');

        // Atur href (tujuan link) tombol Export
        exportBtn.href = exportUrl;

        // ===== Filter Functionality =====
        document.querySelector('.filter-btn').addEventListener('click', () => {
            const filterValue = document.getElementById('filterSelect').value;
            window.location.href = 'laporan.php?filter=' + filterValue;
        });

        // ===== Filter Functionality (Kode asli tetap di sini) =====
        document.querySelector('.filter-btn').addEventListener('click', () => {
            const filterValue = document.getElementById('filterSelect').value;
            window.location.href = 'laporan.php?filter=' + filterValue;
        });

        // ===== Popup Cetak Functionality (Diperbaiki) =====
        const printPopup = document.getElementById('printPopup');
        const showPrintPopupBtn = document.getElementById('showPrintPopup');
        const closePopupBtn = document.querySelector('.close-popup');
        const cancelPrintBtn = document.getElementById('cancelPrint');
        const confirmPrintBtn = document.getElementById('confirmPrint');

        // Tampilkan popup cetak
        showPrintPopupBtn.addEventListener('click', () => {
            printPopup.style.display = 'block';
        });

        // Tutup popup cetak
        function closePrintPopup() {
            printPopup.style.display = 'none';
        }

        closePopupBtn.addEventListener('click', closePrintPopup);
        cancelPrintBtn.addEventListener('click', closePrintPopup);

        // Tutup popup saat klik di luar area popup
        window.addEventListener('click', (event) => {
            if (event.target === printPopup) {
                closePrintPopup();
            }
        });

        // Fungsi cetak laporan
        confirmPrintBtn.addEventListener('click', () => {
            // 1. Dapatkan opsi cetak yang dipilih
            const includeHeader = document.getElementById('includeHeader').checked;
            const includeSummary = document.getElementById('includeSummary').checked;
            const includeDetails = document.getElementById('includeDetails').checked;
            const includeFooter = document.getElementById('includeFooter').checked;

            // 2. Ambil elemen master cetak yang tersembunyi
            const printContentMaster = document.getElementById('printContent');

            // 3. Kloning konten cetak agar tidak memengaruhi master
            const contentToPrint = printContentMaster.cloneNode(true);

            // 4. Sembunyikan/munculkan bagian di dalam klon berdasarkan opsi
            // Menggunakan inline style 'display'
            contentToPrint.querySelector('.print-header').style.display = includeHeader ? 'block' : 'none';
            contentToPrint.querySelector('.print-summary').style.display = includeSummary ? 'flex' : 'none';
            contentToPrint.querySelector('table').style.display = includeDetails ? 'table' : 'none';
            contentToPrint.querySelector('.print-footer').style.display = includeFooter ? 'block' : 'none';

            // 5. Buat wadah cetak sementara dan tambahkan ke body
            const tempPrintContainer = document.createElement('div');
            tempPrintContainer.id = 'temp-print-container';
            tempPrintContainer.classList.add('print-area'); // Agar dikenali oleh CSS @media print
            tempPrintContainer.innerHTML = contentToPrint.innerHTML;
            document.body.appendChild(tempPrintContainer);

            // 6. Sembunyikan elemen utama pada halaman untuk sesi cetak
            // Menambahkan class 'no-print' ke elemen utama yang harus disembunyikan
            document.querySelector('.navbar').classList.add('no-print');
            document.querySelector('.dashboard-container').classList.add('no-print');
            document.querySelector('footer').classList.add('no-print');
            document.getElementById('filterModal').classList.add('no-print');
            document.getElementById('printPopup').classList.add('no-print');

            // 7. Panggil fungsi cetak (browser akan menggunakan @media print)
            window.print();

            // 8. Bersihkan setelah cetak: Hapus wadah sementara dan kelas 'no-print'
            document.body.removeChild(tempPrintContainer);
            document.querySelector('.navbar').classList.remove('no-print');
            document.querySelector('.dashboard-container').classList.remove('no-print');
            document.querySelector('footer').classList.remove('no-print');
            document.getElementById('filterModal').classList.remove('no-print');
            document.getElementById('printPopup').classList.remove('no-print');

            // Tutup popup
            closePrintPopup();
        });

        // ===== POP UP FILTER =====
        document.getElementById("openFilter").onclick = function () {
            document.getElementById("filterModal").style.display = "flex";
        };
        document.getElementById("closeFilter").onclick = function () {
            document.getElementById("filterModal").style.display = "none";
        };

    </script>
</body>

</html>