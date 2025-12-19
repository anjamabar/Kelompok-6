<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
    header("Location: ../../index.php");
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

if ($aksi == 'tambah_sekolah') {
    // Ambil data dari form
    $kd_sklh = $_POST['kd_sklh'];
    $asal_sklh = $_POST['asal_sklh'];
    $kec = $_POST['kec'];
    $kab_kota = $_POST['kab_kota'];
    
    // Cek nama sekolah sudah ada atau belum
    $cek_nama = "SELECT kd_sklh FROM tb_sekolah WHERE asal_sklh = ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("s", $asal_sklh);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: sekolah.php?error=nama_exists");
        exit();
    }
    
    // Insert data sekolah
    $sql = "INSERT INTO tb_sekolah (kd_sklh, asal_sklh, kec, kab_kota) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $kd_sklh, $asal_sklh, $kec, $kab_kota);
    
    if ($stmt->execute()) {
        header("Location: sekolah.php?status=tambah_sukses");
        exit();
    } else {
        header("Location: sekolah.php?error=failed");
        exit();
    }
    
} elseif ($aksi == 'edit_sekolah') {
    // Ambil data dari form
    $kd_sklh = $_POST['kd_sklh'];
    $asal_sklh = $_POST['asal_sklh'];
    $kec = $_POST['kec'];
    $kab_kota = $_POST['kab_kota'];
    
    // Cek nama sekolah sudah ada atau belum (kecuali data yang sedang diedit)
    $cek_nama = "SELECT kd_sklh FROM tb_sekolah WHERE asal_sklh = ? AND kd_sklh != ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("ss", $asal_sklh, $kd_sklh);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: edit_sekolah.php?id=$kd_sklh&error=nama_exists");
        exit();
    }
    
    // Update data sekolah
    $sql = "UPDATE tb_sekolah SET asal_sklh = ?, kec = ?, kab_kota = ? WHERE kd_sklh = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $asal_sklh, $kec, $kab_kota, $kd_sklh);
    
    if ($stmt->execute()) {
        header("Location: sekolah.php?status=edit_sukses");
        exit();
    } else {
        header("Location: edit_sekolah.php?id=$kd_sklh&error=failed");
        exit();
    }
    
} elseif ($aksi == 'tambah_jurusan') {
    // Ambil data dari form
    $kd_jurusan = $_POST['kd_jurusan'];
    $nama_jurusan = $_POST['nama_jurusan'];
    
    // Cek nama jurusan sudah ada atau belum
    $cek_nama = "SELECT kd_jurusan FROM tb_jurusan WHERE nama_jurusan = ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("s", $nama_jurusan);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: jurusan.php?error=nama_exists");
        exit();
    }
    
    // Insert data jurusan
    $sql = "INSERT INTO tb_jurusan (kd_jurusan, nama_jurusan) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $kd_jurusan, $nama_jurusan);
    
    if ($stmt->execute()) {
        header("Location: jurusan.php?status=tambah_sukses");
        exit();
    } else {
        header("Location: jurusan.php?error=failed");
        exit();
    }
    
} elseif ($aksi == 'edit_jurusan') {
    // Ambil data dari form
    $kd_jurusan = $_POST['kd_jurusan'];
    $nama_jurusan = $_POST['nama_jurusan'];
    
    // Cek nama jurusan sudah ada atau belum (kecuali data yang sedang diedit)
    $cek_nama = "SELECT kd_jurusan FROM tb_jurusan WHERE nama_jurusan = ? AND kd_jurusan != ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("ss", $nama_jurusan, $kd_jurusan);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: edit_jurusan.php?id=$kd_jurusan&error=nama_exists");
        exit();
    }
    
    // Update data jurusan
    $sql = "UPDATE tb_jurusan SET nama_jurusan = ? WHERE kd_jurusan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nama_jurusan, $kd_jurusan);
    
    if ($stmt->execute()) {
        header("Location: jurusan.php?status=edit_sukses");
        exit();
    } else {
        header("Location: edit_jurusan.php?id=$kd_jurusan&error=failed");
        exit();
    }
    
} elseif ($aksi == 'tambah_matpel') {
    // Ambil data dari form
    $kd_matpel = $_POST['kd_matpel'];
    $nama_matpel = $_POST['nama_matpel'];
    
    // Cek nama mata pelajaran sudah ada atau belum
    $cek_nama = "SELECT kd_matpel FROM tb_matpel WHERE nama_matpel = ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("s", $nama_matpel);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: matpel.php?error=nama_exists");
        exit();
    }
    
    // Insert data mata pelajaran
    $sql = "INSERT INTO tb_matpel (kd_matpel, nama_matpel) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $kd_matpel, $nama_matpel);
    
    if ($stmt->execute()) {
        header("Location: matpel.php?status=tambah_sukses");
        exit();
    } else {
        header("Location: matpel.php?error=failed");
        exit();
    }
    
} elseif ($aksi == 'edit_matpel') {
    // Ambil data dari form
    $kd_matpel = $_POST['kd_matpel'];
    $nama_matpel = $_POST['nama_matpel'];
    
    // Cek nama mata pelajaran sudah ada atau belum (kecuali data yang sedang diedit)
    $cek_nama = "SELECT kd_matpel FROM tb_matpel WHERE nama_matpel = ? AND kd_matpel != ?";
    $stmt_cek = $conn->prepare($cek_nama);
    $stmt_cek->bind_param("ss", $nama_matpel, $kd_matpel);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows > 0) {
        header("Location: edit_matpel.php?id=$kd_matpel&error=nama_exists");
        exit();
    }
    
    // Update data mata pelajaran
    $sql = "UPDATE tb_matpel SET nama_matpel = ? WHERE kd_matpel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nama_matpel, $kd_matpel);
    
    if ($stmt->execute()) {
        header("Location: matpel.php?status=edit_sukses");
        exit();
    } else {
        header("Location: edit_matpel.php?id=$kd_matpel&error=failed");
        exit();
    }
}

$conn->close();
?>
