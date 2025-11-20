-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 01:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sktb_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pemasukan` decimal(10,2) NOT NULL,
  `pengeluaran` decimal(10,2) NOT NULL,
  `laba` decimal(10,2) NOT NULL,
  `jumlah_transaksi` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemasukan`
--

CREATE TABLE `pemasukan` (
  `id_pemasukan` int(255) NOT NULL,
  `id_transaksi` int(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `sumber` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemasukan`
--

INSERT INTO `pemasukan` (`id_pemasukan`, `id_transaksi`, `tanggal`, `keterangan`, `nominal`, `sumber`) VALUES
(2, 2, '2025-08-25 17:00:00', 'Hawwa - Bouquet bunga', 500000.00, 'Penjualan'),
(3, 3, '2025-08-25 17:00:00', 'hewa - Bouquet bunga', 50000.00, 'Penjualan'),
(4, 4, '2025-08-25 17:00:00', 'hiwi - Bouquet bunga', 400000.00, 'Penjualan'),
(5, 5, '2025-08-25 17:00:00', 'Putrii - Sun Flowers Bouquet', 90000.00, 'Penjualan'),
(6, 6, '2025-08-27 17:00:00', 'hewa - Sun Flowers Bouquet', 45000.00, 'Penjualan'),
(7, 7, '2025-11-04 17:00:00', 'Hawwa - Sun Flowers Bouquet', 45000.00, 'Penjualan'),
(8, 8, '2025-11-19 17:00:00', 'desi - Rose Flowers', 150000.00, 'Penjualan'),
(9, 9, '2025-11-05 17:00:00', 'mawar - Rose Flowers', 100000.00, 'Penjualan'),
(10, 10, '2025-11-06 17:00:00', 'Lintang - Lily Bouquet', 55000.00, 'Penjualan'),
(11, 11, '2025-11-06 17:00:00', 'Dewi - Red Rose Bouquet', 60000.00, 'Penjualan'),
(12, 12, '2025-11-06 17:00:00', 'Langit - Sun Flowers', 50000.00, 'Penjualan'),
(13, 13, '2025-11-06 17:00:00', 'Bumi - Tulip Bouquet', 80000.00, 'Penjualan');

-- --------------------------------------------------------

--
-- Table structure for table `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keterangan` varchar(255) NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengeluaran`
--

INSERT INTO `pengeluaran` (`id_pengeluaran`, `tanggal`, `keterangan`, `nominal`, `kategori`, `catatan`) VALUES
(2, '2025-08-27 17:00:00', 'Beli Pipe CLeaner', 400000.00, 'Bahan Baku', ''),
(3, '2025-11-04 17:00:00', 'Beli bunga', 1000000.00, 'Bahan Baku', '');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `name`, `email`, `password`, `role`) VALUES
(6, 'hewes', 'hawa@gmail.com', '$2y$10$eOB0dlM9b1g3/Rwc3ydQsOtfN.dpYN6DsUMI4GE1UBcNvkLpzi/DO', 'kasir'),
(7, 'hawaaa', 'hewes@gmail.com', '$2y$10$GFO0SW3V6Ggex1Jhs9c6yudjwHd.bY9kzAokXUsACX5ZXs51ICFza', 'admin'),
(8, 'chelsi', 'chelsea@gmail.com', '$2y$10$XuJidooYU1TGVtsCHdYwCePChXCJosfwMQa.vboGzJEfOaDdWEryu', 'kasir');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(255) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `gambar`, `nama_produk`, `kategori`, `harga`, `stok`) VALUES
(1, '690c10166f497.jpeg', 'Sun Flowers', 'Fake Flower', 50000.00, 100),
(2, '690c0ff8562e2.jpeg', 'Red Rose Bouquet', 'Fresh Flower', 60000.00, 100),
(3, '690c0fd30c740.jpeg', 'Lily Bouquet', 'Fresh Flower', 55000.00, 100),
(4, '690c12002d7bf.jpeg', 'Tulip Bouquet', 'Pipe Cleaner', 40000.00, 50);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nama_pelanggan` varchar(255) NOT NULL,
  `produk` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `jumlah` int(255) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `metode` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `nama_pelanggan`, `produk`, `kategori`, `jumlah`, `total`, `metode`) VALUES
(3, '2025-08-25 17:00:00', 'hewa', 'Bouquet bunga', 'Fake Flower', 1, 50000, 'QRIS'),
(4, '2025-08-25 17:00:00', 'hiwi', 'Bouquet bunga', 'Pipe Cleaner', 4, 400000, 'Transfer'),
(5, '2025-08-25 17:00:00', 'Putrii', 'Sun Flowers Bouquet', 'Fresh Flower', 2, 90000, 'Transfer'),
(6, '2025-08-27 17:00:00', 'hewa', 'Sun Flowers Bouquet', 'Fresh Flower', 1, 45000, 'QRIS'),
(7, '2025-11-04 17:00:00', 'Hawwa', 'Sun Flowers Bouquet', 'Fresh Flower', 1, 45000, 'Cash'),
(8, '2025-11-19 17:00:00', 'desi', 'Rose Flowers', 'Pipe Cleaner', 3, 150000, 'QRIS'),
(9, '2025-11-05 17:00:00', 'mawar', 'Rose Flowers', 'Pipe Cleaner', 2, 100000, 'Transfer'),
(10, '2025-11-06 17:00:00', 'Lintang', 'Lily Bouquet', 'Fresh Flower', 1, 55000, 'Cash'),
(11, '2025-11-06 17:00:00', 'Dewi', 'Red Rose Bouquet', 'Fresh Flower', 1, 60000, 'Cash'),
(12, '2025-11-06 17:00:00', 'Langit', 'Sun Flowers', 'Fake Flower', 1, 50000, 'Transfer'),
(13, '2025-11-06 17:00:00', 'Bumi', 'Tulip Bouquet', 'Pipe Cleaner', 2, 80000, 'QRIS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `pemasukan`
--
ALTER TABLE `pemasukan`
  ADD PRIMARY KEY (`id_pemasukan`),
  ADD UNIQUE KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id_pengeluaran`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemasukan`
--
ALTER TABLE `pemasukan`
  MODIFY `id_pemasukan` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id_pengeluaran` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
