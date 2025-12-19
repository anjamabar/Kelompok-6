<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
    header("Location: ../index.php");
    exit();
}

// Koneksi database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "psb_sman6";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil aksi
$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    // Ambil data dari form
    $kd_nilai = $_POST['kd_nilai'];
    $no_daftar = $_POST['no_daftar'];
    $kd_matpel = $_POST['kd_matpel'];
    $nilai_us = $_POST['nilai_us'];
    $nilai_un = $_POST['nilai_un'] ?? 0;
    $jmlh_us = $_POST['jmlh_us'] ?? 0;
    $jmlh_un = $_POST['jmlh_un'] ?? 0;
    
    // Cek kombinasi siswa dan matpel sudah ada atau belum
    $cek_nilai = "SELECT kd_nilai FROM tb_nilai WHERE no_daftar = ? AND kd_matpel = ?";
    $stmt_cek = $conn->prepare($cek_nilai);
    $stmt_cek->bind_param("ss", $no_daftar, $kd_matpel);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: nilai.php?error=duplicate");
        exit();
    }
    
    // Insert data nilai
    $sql = "INSERT INTO tb_nilai (kd_nilai, no_daftar, kd_matpel, nilai_us, nilai_un, jmlh_us, jmlh_un) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiddi", $kd_nilai, $no_daftar, $kd_matpel, $nilai_us, $nilai_un, $jmlh_us, $jmlh_un);
    
    if ($stmt->execute()) {
        header("Location: nilai.php?status=tambah_sukses");
        exit();
    } else {
        header("Location: nilai.php?error=failed");
        exit();
    }
    
} elseif ($aksi == 'edit') {
    // Ambil data dari form
    $kd_nilai = $_POST['kd_nilai'];
    $no_daftar = $_POST['no_daftar'];
    $kd_matpel = $_POST['kd_matpel'];
    $nilai_us = $_POST['nilai_us'];
    $nilai_un = $_POST['nilai_un'] ?? 0;
    $jmlh_us = $_POST['jmlh_us'] ?? 0;
    $jmlh_un = $_POST['jmlh_un'] ?? 0;
    
    // Cek kombinasi siswa dan matpel sudah ada atau belum (kecuali data yang sedang diedit)
    $cek_nilai = "SELECT kd_nilai FROM tb_nilai WHERE no_daftar = ? AND kd_matpel = ? AND kd_nilai != ?";
    $stmt_cek = $conn->prepare($cek_nilai);
    $stmt_cek->bind_param("sss", $no_daftar, $kd_matpel, $kd_nilai);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: edit_nilai.php?id=$kd_nilai&error=duplicate");
        exit();
    }
    
    // Update data nilai
    $sql = "UPDATE tb_nilai SET no_daftar = ?, kd_matpel = ?, nilai_us = ?, nilai_un = ?, jmlh_us = ?, jmlh_un = ? WHERE kd_nilai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiddii", $no_daftar, $kd_matpel, $nilai_us, $nilai_un, $jmlh_us, $jmlh_un, $kd_nilai);
    
    if ($stmt->execute()) {
        header("Location: nilai.php?status=edit_sukses");
        exit();
    } else {
        header("Location: edit_nilai.php?id=$kd_nilai&error=failed");
        exit();
    }
}

$conn->close();
?>
