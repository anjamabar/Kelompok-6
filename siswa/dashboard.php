<?php
session_start();

// Cek apakah user sudah login dan role-nya siswa
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'siswa') {
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

// Ambil data siswa berdasarkan user yang login
$siswa_data = null;
$no_daftar = null;

// Cari data pendaftar berdasarkan id_user
$sql_siswa = "SELECT p.*, s.asal_sklh, j.nama_jurusan 
             FROM tb_pendaftar p 
             LEFT JOIN tb_sekolah s ON p.kd_sklh = s.kd_sklh 
             LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan 
             WHERE p.id_user = ?";
$stmt = $conn->prepare($sql_siswa);
$stmt->bind_param("i", $_SESSION['id_user']);
$stmt->execute();
$result_siswa = $stmt->get_result();

if ($result_siswa->num_rows > 0) {
    $siswa_data = $result_siswa->fetch_assoc();
    $no_daftar = $siswa_data['no_daftar'];
}

// Hitung status berkas
$berkas_diterima = 0;
$berkas_menunggu = 0;
$berkas_ditolak = 0;
$foto_profil = null;

if ($no_daftar) {
    $sql_berkas = "SELECT status, COUNT(*) as total FROM tb_berkas_pendaftaran WHERE no_daftar = ? GROUP BY status";
    $stmt_berkas = $conn->prepare($sql_berkas);
    $stmt_berkas->bind_param("s", $no_daftar);
    $stmt_berkas->execute();
    $result_berkas = $stmt_berkas->get_result();
    
    while ($row = $result_berkas->fetch_assoc()) {
        switch ($row['status']) {
            case 'diterima':
                $berkas_diterima = $row['total'];
                break;
            case 'menunggu':
                $berkas_menunggu = $row['total'];
                break;
            case 'ditolak':
                $berkas_ditolak = $row['total'];
                break;
        }
    }
    $stmt_berkas->close();
    
    // Ambil foto profil
    $sql_foto = "SELECT nama_file FROM tb_berkas_pendaftaran WHERE no_daftar = ? AND jenis_berkas = 'foto' AND status = 'diterima' ORDER BY tanggal_upload DESC LIMIT 1";
    $stmt_foto = $conn->prepare($sql_foto);
    $stmt_foto->bind_param("s", $no_daftar);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    
    if ($result_foto->num_rows > 0) {
        $foto_data = $result_foto->fetch_assoc();
        $foto_profil = "../berkas/" . $foto_data['nama_file'];
    }
    $stmt_foto->close();
}

// Simpan foto profil ke session untuk digunakan di sidebar
$_SESSION['foto_profil'] = $foto_profil;

// Ambil data nilai jika ada
$nilai_data = null;
if ($no_daftar && $siswa_data['kd_nilai']) {
    $sql_nilai = "SELECT n.*, m.nama_matpel 
                  FROM tb_nilai n 
                  LEFT JOIN tb_matpel m ON n.kd_matpel = m.kd_matpel 
                  WHERE n.kd_nilai = ?";
    $stmt_nilai = $conn->prepare($sql_nilai);
    $stmt_nilai->bind_param("s", $siswa_data['kd_nilai']);
    $stmt_nilai->execute();
    $result_nilai = $stmt_nilai->get_result();
    
    if ($result_nilai->num_rows > 0) {
        $nilai_data = $result_nilai->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PSB SMAN 6</title>
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
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            background-color: #f8f9fc;
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
            object-fit: cover;
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
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .card h3 {
            margin: 10px 0 5px 0;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .card p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .info-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .info-data {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-menunggu {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-diterima {
            background: #d4edda;
            color: #155724;
        }
        
        .status-ditolak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Dashboard Siswa</h2>
            <div class="user-info">
                <?php if ($foto_profil && file_exists($foto_profil)): ?>
                    <img src="<?php echo htmlspecialchars($foto_profil); ?>" alt="User">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_user']); ?>" alt="User">
                <?php endif; ?>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['nama_user']); ?></h4>
                    <p>Siswa</p>
                </div>
            </div>
        </div>

        <?php if ($siswa_data): ?>
            <!-- Status Pendaftaran -->
            <div class="info-section">
                <h3 class="section-title">Status Pendaftaran</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">No. Pendaftaran</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['no_daftar']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-data">
                            <span class="status-badge status-<?php echo $siswa_data['status']; ?>">
                                <?php echo ucfirst($siswa_data['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jurusan</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nama_jurusan'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Sekolah Asal</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['asal_sklh'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-file-upload"></i>
                    <h3>Status Berkas</h3>
                    <p><?php echo $berkas_diterima + $berkas_menunggu + $berkas_ditolak; ?> Berkas</p>
                </div>
                <div class="card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Berkas Diterima</h3>
                    <p><?php echo $berkas_diterima; ?> Berkas</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h3>Berkas Menunggu</h3>
                    <p><?php echo $berkas_menunggu; ?> Berkas</p>
                </div>
                <div class="card">
                    <i class="fas fa-times-circle"></i>
                    <h3>Berkas Ditolak</h3>
                    <p><?php echo $berkas_ditolak; ?> Berkas</p>
                </div>
            </div>

            <!-- Data Pribadi -->
            <div class="info-section">
                <h3 class="section-title">Data Pribadi</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nama_clnsiswa']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NISN</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nisn']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIK</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nik']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-data"><?php echo $siswa_data['jenis_k'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tempat Lahir</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['tempat_lhr']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Lahir</div>
                        <div class="info-data"><?php echo date('d/m/Y', strtotime($siswa_data['tgl_lhr'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Agama</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['agama']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. HP/WhatsApp</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['no_telphp']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['email']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="info-section">
                <h3 class="section-title">Data Orang Tua</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Orang Tua</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nama_ortu']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pekerjaan</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['pekerjaan_ortu']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Alamat</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['alamat_ortu']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kelurahan</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['kelurahan']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kecamatan</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['kecamatan']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kabupaten</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['kabupaten']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Data Nilai -->
            <?php if ($nilai_data): ?>
            <div class="info-section">
                <h3 class="section-title">Data Nilai</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Nilai US</th>
                            <th>Nilai UN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nilai_data as $nilai): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nilai['nama_matpel']); ?></td>
                            <td><?php echo $nilai['nilai_us'] ?? '-'; ?></td>
                            <td><?php echo $nilai['nilai_un'] ?? '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Alert untuk siswa yang belum mendaftar -->
            <div class="alert-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Belum Ada Data Pendaftaran</h4>
                <p>Anda belum melakukan pendaftaran. Silakan hubungi panitia untuk informasi lebih lanjut.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
