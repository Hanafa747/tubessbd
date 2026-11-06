-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 05:02 PM
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
-- Database: `db_rumah_sakit`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun_admin`
--

CREATE TABLE `akun_admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrator','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun_admin`
--

INSERT INTO `akun_admin` (`id_admin`, `nama_admin`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$10$MOw.MOAuct0GN0qvIaJ5z.d3.dEykSaUjhkRnE0uD1/pt0jc2AHva', 'administrator'),
(2, 'Admin Rumah Sakit 1', 'admin1', '$2y$10$GHuFz2MGrd9VHtlsIaBD7e2igJ397/l0cfp5x9zu0X8Atk49Q643O', 'admin'),
(3, 'Admin Rumah Sakit 2', 'admin2', 'admin123', 'admin'),
(4, 'Admin Rumah Sakit 3', 'admin3', 'admin123', 'admin'),
(5, 'Admin Rumah Sakit 4', 'admin4', 'admin123', 'admin'),
(6, 'Admin Rumah Sakit 5', 'admin5', 'admin123', 'admin'),
(7, 'Admin Rumah Sakit 6', 'admin6', 'admin123', 'admin'),
(8, 'Admin Rumah Sakit 7', 'admin7', 'admin123', 'admin'),
(9, 'Admin Rumah Sakit 8', 'admin8', 'admin123', 'admin'),
(10, 'Admin Rumah Sakit 9', 'admin9', 'admin123', 'admin'),
(11, 'Admin Rumah Sakit 10', 'admin10', '$2y$10$wtP6EdYRU2aasjFjIoCM0.tqWgf5HOE6N.Wp9MkJykNIejlvBHBTO', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `antrean`
--

CREATE TABLE `antrean` (
  `id_antrean` int(11) NOT NULL,
  `id_pendaftaran` int(11) NOT NULL,
  `id_poli` int(11) NOT NULL,
  `nomor_antrean` int(11) NOT NULL,
  `tanggal_antrean` date NOT NULL,
  `status` enum('Menunggu','Diperiksa','Selesai','Batal') NOT NULL DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `antrean`
--

INSERT INTO `antrean` (`id_antrean`, `id_pendaftaran`, `id_poli`, `nomor_antrean`, `tanggal_antrean`, `status`, `created_at`) VALUES
(1, 4, 4, 1, '2026-10-23', 'Batal', '2026-11-06 14:02:20'),
(2, 9, 9, 1, '2026-10-28', 'Selesai', '2026-11-06 14:04:04');

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int(11) NOT NULL,
  `id_poli` int(11) NOT NULL,
  `nama_dokter` varchar(100) NOT NULL,
  `spesialisasi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `id_poli`, `nama_dokter`, `spesialisasi`) VALUES
(1, 1, 'Dr. Ahmad Fauzi', 'Dokter Umum'),
(2, 2, 'Dr. Rina Marlina', 'Dokter Gigi'),
(3, 3, 'Dr. Budi Hartono', 'Spesialis Anak'),
(4, 4, 'Dr. Siska Amelia', 'Spesialis Jantung'),
(5, 5, 'Dr. Deni Saputra', 'Spesialis Mata'),
(6, 6, 'Dr. Maya Lestari', 'Spesialis Kulit'),
(7, 7, 'Dr. Rudi Kurniawan', 'Spesialis Saraf'),
(8, 8, 'Dr. Nanda Putri', 'Spesialis THT'),
(9, 9, 'Dr. Fajar Nugraha', 'Spesialis Penyakit Dalam'),
(10, 10, 'Dr. Citra Dewi', 'Spesialis Bedah');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int(11) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id_pasien`, `nama_pasien`, `jenis_kelamin`, `tanggal_lahir`, `alamat`) VALUES
(1, 'Andi Saputra', 'Laki-laki', '2000-05-12', 'Jl. Merdeka, Bengkulu'),
(2, 'Siti Amelia', 'Perempuan', '1998-08-21', 'Jl. Suprapto, Bengkulu'),
(3, 'Budi Santoso', 'Laki-laki', '1995-03-15', 'Jl. Rafflesia, Bengkulu'),
(4, 'Dewi Lestari', 'Perempuan', '2001-11-09', 'Jl. Danau, Bengkulu'),
(5, 'Rizky Pratama', 'Laki-laki', '1999-06-17', 'Jl. Panorama, Bengkulu'),
(6, 'Putri Maharani', 'Perempuan', '2002-02-25', 'Jl. Hibrida, Bengkulu'),
(7, 'Fajar Ramadhan', 'Laki-laki', '1997-09-30', 'Jl. S. Parman, Bengkulu'),
(8, 'Nadia Safitri', 'Perempuan', '2000-12-05', 'Jl. Cendana, Bengkulu'),
(9, 'Dimas Kurniawan', 'Laki-laki', '1996-04-18', 'Jl. Kebun Tebeng, Bengkulu'),
(10, 'Aulia Rahma', 'Perempuan', '2003-07-22', 'Jl. Padang Jati, Bengkulu');

-- --------------------------------------------------------

--
-- Table structure for table `pemulihan_password`
--

CREATE TABLE `pemulihan_password` (
  `id` tinyint(4) NOT NULL,
  `kode_hash` varchar(255) NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `id_dokter` int(11) NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `status` enum('Menunggu','Diperiksa','Selesai','Batal') DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_pasien`, `id_dokter`, `tanggal_daftar`, `status`) VALUES
(1, 1, 1, '2026-10-20', 'Selesai'),
(2, 2, 2, '2026-10-21', 'Selesai'),
(3, 3, 3, '2026-10-22', 'Selesai'),
(4, 4, 4, '2026-10-23', 'Batal'),
(5, 5, 5, '2026-10-24', 'Selesai'),
(6, 6, 6, '2026-10-25', 'Menunggu'),
(7, 7, 7, '2026-10-26', 'Selesai'),
(8, 8, 8, '2026-10-27', 'Diperiksa'),
(9, 9, 9, '2026-10-28', 'Selesai'),
(10, 10, 10, '2026-10-29', 'Selesai');

-- --------------------------------------------------------

--
-- Table structure for table `poli`
--

CREATE TABLE `poli` (
  `id_poli` int(11) NOT NULL,
  `nama_poli` varchar(100) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poli`
--

INSERT INTO `poli` (`id_poli`, `nama_poli`, `lokasi`) VALUES
(1, 'Poli Umum', 'Gedung A Lantai 1'),
(2, 'Poli Gigi', 'Gedung A Lantai 2'),
(3, 'Poli Anak', 'Gedung B Lantai 1'),
(4, 'Poli Jantung', 'Gedung B Lantai 2'),
(5, 'Poli Mata', 'Gedung C Lantai 1'),
(6, 'Poli Kulit', 'Gedung C Lantai 2'),
(7, 'Poli Saraf', 'Gedung D Lantai 1'),
(8, 'Poli THT', 'Gedung D Lantai 2'),
(9, 'Poli Penyakit Dalam', 'Gedung E Lantai 1'),
(10, 'Poli Bedah', 'Gedung E Lantai 2');

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `id_rekam` int(11) NOT NULL,
  `id_pendaftaran` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `tindakan` text DEFAULT NULL,
  `resep_obat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rekam_medis`
--

INSERT INTO `rekam_medis` (`id_rekam`, `id_pendaftaran`, `diagnosis`, `tindakan`, `resep_obat`) VALUES
(1, 1, 'Demam dan flu', 'Pemeriksaan suhu tubuh dan kondisi pasien', 'Paracetamol'),
(2, 2, 'Gigi berlubang', 'Penambalan gigi', 'Obat pereda nyeri'),
(3, 3, 'Demam pada anak', 'Pemeriksaan fisik anak', 'Paracetamol sirup'),
(4, 4, 'Gangguan irama jantung', 'Pemeriksaan EKG', 'Sesuai resep dokter'),
(5, 5, 'Rabun jauh', 'Pemeriksaan ketajaman penglihatan', 'Kacamata minus'),
(6, 6, 'Alergi kulit', 'Pemeriksaan kondisi kulit', 'Salep antihistamin'),
(7, 7, 'Migrain', 'Pemeriksaan saraf', 'Obat pereda nyeri'),
(8, 8, 'Infeksi telinga', 'Pemeriksaan telinga', 'Obat tetes telinga'),
(9, 9, 'Gangguan pencernaan', 'Pemeriksaan kondisi perut', 'Obat maag'),
(10, 10, 'Radang usus buntu', 'Pemeriksaan dan tindakan bedah', 'Sesuai resep dokter');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun_admin`
--
ALTER TABLE `akun_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `antrean`
--
ALTER TABLE `antrean`
  ADD PRIMARY KEY (`id_antrean`),
  ADD UNIQUE KEY `id_pendaftaran` (`id_pendaftaran`),
  ADD UNIQUE KEY `uq_antrean_poli_tanggal_nomor` (`id_poli`,`tanggal_antrean`,`nomor_antrean`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`);

--
-- Indexes for table `pemulihan_password`
--
ALTER TABLE `pemulihan_password`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indexes for table `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id_poli`),
  ADD UNIQUE KEY `nama_poli` (`nama_poli`);

--
-- Indexes for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`id_rekam`),
  ADD UNIQUE KEY `id_pendaftaran` (`id_pendaftaran`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `akun_admin`
--
ALTER TABLE `akun_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `antrean`
--
ALTER TABLE `antrean`
  MODIFY `id_antrean` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `poli`
--
ALTER TABLE `poli`
  MODIFY `id_poli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `id_rekam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrean`
--
ALTER TABLE `antrean`
  ADD CONSTRAINT `fk_antrean_pendaftaran` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_antrean_poli` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON UPDATE CASCADE;

--
-- Constraints for table `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON UPDATE CASCADE;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON UPDATE CASCADE;

--
-- Constraints for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
