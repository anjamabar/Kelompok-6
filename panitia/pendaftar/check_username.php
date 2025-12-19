<?php
// Koneksi database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "psb_sman6";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil username dari parameter GET
$username = isset($_GET['username']) ? $conn->real_escape_string($_GET['username']) : '';

// Cek ketersediaan username
$sql = "SELECT id_user FROM tb_user WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Kirim respon dalam format JSON
header('Content-Type: application/json');
echo json_encode([
    'available' => $result->num_rows === 0,
    'username' => $username
]);

$stmt->close();
$conn->close();
?>
