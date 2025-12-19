<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
    header("Location: ../login.php");
    exit();
}

// Koneksi database (disiapkan untuk pengembangan fitur laporan)
$host = "localhost";
$user = "root";
$password = "";
$dbname = "psb_sman6";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - PSB SMAN6</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background-color: #f8f9fc;
            position: relative;
            z-index: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-details h4 {
            margin: 0;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .user-details p {
            margin: 2px 0 0 0;
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        .info-box {
            background: white;
            border-radius: 8px;
            padding: 40px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
        }

        .info-box i {
            font-size: 3rem;
            color: #bdc3c7;
            margin-bottom: 15px;
        }

        .info-box h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .info-box p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .badge-dev {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            background: #f1c40f1a;
            color: #f39c12;
            font-size: 0.8rem;
            border: 1px solid #f1c40f;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/sidebar_f.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>seleksi</h2>
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_user']); ?>" alt="User">
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['nama_user']); ?></h4>
                    <p><?php echo ucfirst($_SESSION['role']); ?></p>
                </div>
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-tools"></i>
            <h3>Fitur seleksi Sedang Dalam Tahap Pengembangan</h3>
            <p>Halaman ini belum sepenuhnya tersedia.</p>
            <p>Silakan kembali lagi nanti setelah pengembangan selesai.</p>
            <span class="badge-dev">Status: Dalam Pengembangan</span>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
