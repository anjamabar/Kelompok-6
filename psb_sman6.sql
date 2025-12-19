-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 09 Des 2025 pada 21.15
-- Versi server: 8.0.30
-- Versi PHP: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `psb_sman6`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_berkas_pendaftaran`
--

CREATE TABLE `tb_berkas_pendaftaran` (
  `id_berkas` int NOT NULL,
  `no_daftar` varchar(5) NOT NULL,
  `jenis_berkas` enum('ijazah','kk','akta','skhu','foto') NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('diterima','ditolak','menunggu') DEFAULT 'menunggu',
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_berkas_pendaftaran`
--

INSERT INTO `tb_berkas_pendaftaran` (`id_berkas`, `no_daftar`, `jenis_berkas`, `nama_file`, `tanggal_upload`, `status`, `keterangan`) VALUES
(1, '001', 'foto', '001_foto_1765314477_b45166e5.jpg', '2025-12-09 21:07:57', 'menunggu', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_jadwal_daftar_ulang`
--

CREATE TABLE `tb_jadwal_daftar_ulang` (
  `id` int NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_jurusan`
--

CREATE TABLE `tb_jurusan` (
  `kd_jurusan` varchar(5) NOT NULL,
  `nama_jurusan` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_jurusan`
--

INSERT INTO `tb_jurusan` (`kd_jurusan`, `nama_jurusan`) VALUES
('JR001', 'cihuy');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_matpel`
--

CREATE TABLE `tb_matpel` (
  `kd_matpel` varchar(5) NOT NULL,
  `nama_matpel` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_matpel`
--

INSERT INTO `tb_matpel` (`kd_matpel`, `nama_matpel`) VALUES
('MT001', 'ambatukaaaam');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_nilai`
--

CREATE TABLE `tb_nilai` (
  `kd_nilai` varchar(5) NOT NULL,
  `no_daftar` varchar(5) DEFAULT NULL,
  `kd_matpel` varchar(5) DEFAULT NULL,
  `nilai_us` double DEFAULT NULL,
  `nilai_un` double DEFAULT NULL,
  `jmlh_us` double DEFAULT NULL,
  `jmlh_un` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pendaftar`
--

CREATE TABLE `tb_pendaftar` (
  `no_daftar` varchar(5) NOT NULL,
  `nama_clnsiswa` varchar(30) DEFAULT NULL,
  `nisn` varchar(30) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `jenis_k` varchar(1) DEFAULT NULL,
  `tempat_lhr` varchar(30) DEFAULT NULL,
  `tgl_lhr` date DEFAULT NULL,
  `anak_ke` int DEFAULT NULL,
  `tinggi_badan` int DEFAULT NULL,
  `berat_badan` int DEFAULT NULL,
  `agama` varchar(15) DEFAULT NULL,
  `no_telphp` varchar(12) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `nama_ortu` varchar(35) DEFAULT NULL,
  `alamat_ortu` varchar(100) DEFAULT NULL,
  `kelurahan` varchar(35) DEFAULT NULL,
  `kecamatan` varchar(35) DEFAULT NULL,
  `kabupaten` varchar(35) DEFAULT NULL,
  `kota` varchar(35) DEFAULT NULL,
  `pekerjaan_ortu` varchar(50) DEFAULT NULL,
  `tahun_masuk` varchar(4) DEFAULT NULL,
  `kd_sklh` varchar(15) DEFAULT NULL,
  `kd_jurusan` varchar(5) DEFAULT NULL,
  `kd_nilai` varchar(5) DEFAULT NULL,
  `status` enum('diterima','tidak','menunggu') DEFAULT 'menunggu',
  `foto` varchar(255) DEFAULT NULL,
  `id_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_pendaftar`
--

INSERT INTO `tb_pendaftar` (`no_daftar`, `nama_clnsiswa`, `nisn`, `nik`, `no_kk`, `jenis_k`, `tempat_lhr`, `tgl_lhr`, `anak_ke`, `tinggi_badan`, `berat_badan`, `agama`, `no_telphp`, `email`, `nama_ortu`, `alamat_ortu`, `kelurahan`, `kecamatan`, `kabupaten`, `kota`, `pekerjaan_ortu`, `tahun_masuk`, `kd_sklh`, `kd_jurusan`, `kd_nilai`, `status`, `foto`, `id_user`) VALUES
('001', '23', '213', '123', '234', 'L', '1234', '2025-12-05', NULL, NULL, NULL, 'Kristen', '2134', '12@gmail.com', '321', '134', '1234', '1234', '1234', '21341', '1234', '2025', 'SKL001', 'JR001', NULL, 'menunggu', NULL, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_sekolah`
--

CREATE TABLE `tb_sekolah` (
  `kd_sklh` varchar(15) NOT NULL,
  `asal_sklh` varchar(50) DEFAULT NULL,
  `kec` varchar(30) DEFAULT NULL,
  `kab_kota` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_sekolah`
--

INSERT INTO `tb_sekolah` (`kd_sklh`, `asal_sklh`, `kec`, `kab_kota`) VALUES
('SKL001', 'smk tawuran 13 kaluku', 'mars selatan', 'mars pusat');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_user` varchar(50) DEFAULT NULL,
  `role` enum('panitia','siswa','kepala_sekolah') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'panitia',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `username`, `password`, `nama_user`, `role`, `status`) VALUES
(1, 'panitia', 'panitia#1234', 'pekok', 'panitia', 'aktif'),
(5, '12345678', '12345678', '23', 'siswa', 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tb_berkas_pendaftaran`
--
ALTER TABLE `tb_berkas_pendaftaran`
  ADD PRIMARY KEY (`id_berkas`),
  ADD KEY `fk_berkas_pendaftar` (`no_daftar`);

--
-- Indeks untuk tabel `tb_jadwal_daftar_ulang`
--
ALTER TABLE `tb_jadwal_daftar_ulang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_jurusan`
--
ALTER TABLE `tb_jurusan`
  ADD PRIMARY KEY (`kd_jurusan`);

--
-- Indeks untuk tabel `tb_matpel`
--
ALTER TABLE `tb_matpel`
  ADD PRIMARY KEY (`kd_matpel`);

--
-- Indeks untuk tabel `tb_nilai`
--
ALTER TABLE `tb_nilai`
  ADD PRIMARY KEY (`kd_nilai`),
  ADD KEY `no_daftar` (`no_daftar`),
  ADD KEY `kd_matpel` (`kd_matpel`);

--
-- Indeks untuk tabel `tb_pendaftar`
--
ALTER TABLE `tb_pendaftar`
  ADD PRIMARY KEY (`no_daftar`),
  ADD KEY `kd_sklh` (`kd_sklh`),
  ADD KEY `kd_jurusan` (`kd_jurusan`),
  ADD KEY `fk_pendaftar_user` (`id_user`);

--
-- Indeks untuk tabel `tb_sekolah`
--
ALTER TABLE `tb_sekolah`
  ADD PRIMARY KEY (`kd_sklh`);

--
-- Indeks untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tb_berkas_pendaftaran`
--
ALTER TABLE `tb_berkas_pendaftaran`
  MODIFY `id_berkas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tb_jadwal_daftar_ulang`
--
ALTER TABLE `tb_jadwal_daftar_ulang`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tb_berkas_pendaftaran`
--
ALTER TABLE `tb_berkas_pendaftaran`
  ADD CONSTRAINT `fk_berkas_pendaftar` FOREIGN KEY (`no_daftar`) REFERENCES `tb_pendaftar` (`no_daftar`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_nilai`
--
ALTER TABLE `tb_nilai`
  ADD CONSTRAINT `tb_nilai_ibfk_1` FOREIGN KEY (`no_daftar`) REFERENCES `tb_pendaftar` (`no_daftar`),
  ADD CONSTRAINT `tb_nilai_ibfk_2` FOREIGN KEY (`kd_matpel`) REFERENCES `tb_matpel` (`kd_matpel`);

--
-- Ketidakleluasaan untuk tabel `tb_pendaftar`
--
ALTER TABLE `tb_pendaftar`
  ADD CONSTRAINT `fk_pendaftar_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_pendaftar_ibfk_1` FOREIGN KEY (`kd_sklh`) REFERENCES `tb_sekolah` (`kd_sklh`),
  ADD CONSTRAINT `tb_pendaftar_ibfk_2` FOREIGN KEY (`kd_jurusan`) REFERENCES `tb_jurusan` (`kd_jurusan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
