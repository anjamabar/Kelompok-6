<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
    header("Location: ../login.php");
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

// Ambil data statistik
$total_pendaftar = 0;
$total_diterima = 0;
$total_ditolak = 0;
$total_sekolah = 0;
$total_jurusan = 0;
$total_matpel = 0;
$total_nilai = 0;

// Total pendaftar
$sql_pendaftar = "SELECT COUNT(*) as total FROM tb_pendaftar";
$result_pendaftar = $conn->query($sql_pendaftar);
if ($result_pendaftar) {
    $total_pendaftar = $result_pendaftar->fetch_assoc()['total'];
}

// Total sekolah
$sql_sekolah = "SELECT COUNT(*) as total FROM tb_sekolah";
$result_sekolah = $conn->query($sql_sekolah);
if ($result_sekolah) {
    $total_sekolah = $result_sekolah->fetch_assoc()['total'];
}

// Total jurusan
$sql_jurusan = "SELECT COUNT(*) as total FROM tb_jurusan";
$result_jurusan = $conn->query($sql_jurusan);
if ($result_jurusan) {
    $total_jurusan = $result_jurusan->fetch_assoc()['total'];
}

// Total mata pelajaran
$sql_matpel = "SELECT COUNT(*) as total FROM tb_matpel";
$result_matpel = $conn->query($sql_matpel);
if ($result_matpel) {
    $total_matpel = $result_matpel->fetch_assoc()['total'];
}

// Total nilai
$sql_nilai = "SELECT COUNT(*) as total FROM tb_nilai";
$result_nilai = $conn->query($sql_nilai);
if ($result_nilai) {
    $total_nilai = $result_nilai->fetch_assoc()['total'];
}

// Persentase (dummy data karena tidak ada field status)
$persentase = $total_pendaftar > 0 ? round(($total_diterima / $total_pendaftar) * 100, 1) : 0;

// Ambil data pendaftar terbaru
$recent_sql = "SELECT p.*, s.asal_sklh, j.nama_jurusan 
               FROM tb_pendaftar p 
               LEFT JOIN tb_sekolah s ON p.kd_sklh = s.kd_sklh 
               LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan 
               ORDER BY p.no_daftar DESC 
               LIMIT 5";
$recent_result = $conn->query($recent_sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Panitia - PSB SMAN6</title>
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
        
        /* Main content area */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background-color: #f8f9fc;
            position: relative;
            z-index: 1;
        }
        
        .menu-item {
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            color: #ecf0f1;
            text-decoration: none;
            display: block;
        }
        
        .menu-item:hover, .menu-item.active {
            background: #3498db;
            color: white;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
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
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .card i {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .card h3 {
            margin: 10px 0 5px 0;
            color: #2c3e50;
        }
        
        .card p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .recent-activity {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f2f6;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #3498db;
        }
        
        .activity-details h4 {
            margin: 0 0 5px 0;
            font-size: 0.9rem;
            color: #2c3e50;
        }
        
        .activity-details p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.8rem;
        }
        
        .time-ago {
            color: #95a5a6;
            font-size: 0.75rem;
            margin-left: auto;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Include Sidebar (Styles + Content) -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Dashboard</h2>
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_user']); ?>" alt="User">
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['nama_user']); ?></h4>
                    <p><?php echo ucfirst($_SESSION['role']); ?></p>
                </div>
               
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <i class="fas fa-users"></i>
                <h3>Total Pendaftar</h3>
                <p><?php echo $total_pendaftar; ?> Orang</p>
            </div>
            <div class="card">
                <i class="fas fa-school"></i>
                <h3>Data Sekolah</h3>
                <p><?php echo $total_sekolah; ?> Sekolah</p>
            </div>
            <div class="card">
                <i class="fas fa-graduation-cap"></i>
                <h3>Data Jurusan</h3>
                <p><?php echo $total_jurusan; ?> Jurusan</p>
            </div>
            <div class="card">
                <i class="fas fa-book"></i>
                <h3>Data Matpel</h3>
                <p><?php echo $total_matpel; ?> Matpel</p>
            </div>
        </div>

        <div class="recent-activity">
            <h3 class="section-title">Pendaftar Terbaru</h3>
            <?php
            if ($recent_result && $recent_result->num_rows > 0) {
                while ($row = $recent_result->fetch_assoc()) {
                    echo '<div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-details">
                                <h4>' . htmlspecialchars($row['nama_clnsiswa']) . '</h4>
                                <p>Asal: ' . htmlspecialchars($row['asal_sklh'] ?? '-') . ' | Jurusan: ' . htmlspecialchars($row['nama_jurusan'] ?? '-') . '</p>
                            </div>
                            <span class="time-ago">No: ' . htmlspecialchars($row['no_daftar']) . '</span>
                          </div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 20px; color: #666;">
                        <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px; color: #ddd;"></i>
                        <p>Belum ada data pendaftar</p>
                      </div>';
            }
            ?>
        </div>
    </div>

    <script>
        // Update waktu secara real-time
        function updateTimeAgo() {
            const timeElements = document.querySelectorAll('.time-ago');
            timeElements.forEach(el => {
                // Ini hanya contoh, di implementasi nyata gunakan library seperti moment.js
                // untuk menghitung perbedaan waktu yang lebih akurat
                const timeText = el.textContent;
                if (timeText.includes('menit')) {
                    const minutes = parseInt(timeText);
                    el.textContent = `${minutes + 1} menit yang lalu`;
                }
            });
        }

        // Update waktu setiap menit
        setInterval(updateTimeAgo, 60000);
    </script>
</body>
</html>

<?php
$conn->close();
?>
