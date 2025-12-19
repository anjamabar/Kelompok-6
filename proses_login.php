<?php
session_start();

// Koneksi database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "psb_sman6";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Query untuk cek user
$sql = "SELECT * FROM tb_user WHERE username = ? AND status = 'aktif'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    
    // Verifikasi password (plain text comparison, sesuai database)
    if ($password === $user_data['password']) {
        // Login berhasil, buat session
        $_SESSION['id_user'] = $user_data['id_user'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama_user'] = $user_data['nama_user'];
        $_SESSION['role'] = $user_data['role'];
        
        // Redirect berdasarkan role
        switch ($user_data['role']) {
            case 'panitia':
                header("Location: panitia/dashboard.php");
                break;
            case 'siswa':
                header("Location: siswa/dashboard.php");
                break;
            case 'kepala_sekolah':
                header("Location: kepsek/dashboard.php");
                break;
            default:
                // Jika role tidak dikenali, redirect ke halaman login dengan pesan error
                header("Location: index.php?error=3");
                break;
        }
        exit();
    } else {
        // Password salah
        header("Location: index.php?error=1");
        exit();
    }
} else {
    // Username tidak ditemukan
    header("Location: index.php?error=2");
    exit();
}

$stmt->close();
$conn->close();
?>