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
$sql_siswa = "SELECT p.*, s.asal_sklh, s.kec as sekolah_kec, s.kab_kota as sekolah_kab, j.nama_jurusan 
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

// Proses update profil
$update_message = '';
$update_status = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $no_daftar) {
    $email = $_POST['email'];
    $no_telphp = $_POST['no_telphp'];
    $nama_ortu = $_POST['nama_ortu'];
    $pekerjaan_ortu = $_POST['pekerjaan_ortu'];
    $alamat_ortu = $_POST['alamat_ortu'];
    $kelurahan = $_POST['kelurahan'];
    $kecamatan = $_POST['kecamatan'];
    $kabupaten = $_POST['kabupaten'];
    $kota = $_POST['kota'];
    
    // Validasi input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_status = 'error';
        $update_message = 'Format email tidak valid.';
    } elseif (!preg_match('/^[0-9]{10,13}$/', $no_telphp)) {
        $update_status = 'error';
        $update_message = 'Nomor telepon harus terdiri dari 10-13 digit angka.';
    } else {
        // Update data ke database
        $sql_update = "UPDATE tb_pendaftar SET 
                      email = ?, 
                      no_telphp = ?, 
                      nama_ortu = ?, 
                      pekerjaan_ortu = ?, 
                      alamat_ortu = ?, 
                      kelurahan = ?, 
                      kecamatan = ?, 
                      kabupaten = ?, 
                      kota = ? 
                      WHERE no_daftar = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssssssssss", $email, $no_telphp, $nama_ortu, $pekerjaan_ortu, 
                                $alamat_ortu, $kelurahan, $kecamatan, $kabupaten, $kota, $no_daftar);
        
        if ($stmt_update->execute()) {
            $update_status = 'success';
            $update_message = 'Data profil berhasil diperbarui!';
            
            // Refresh data siswa
            $stmt = $conn->prepare($sql_siswa);
            $stmt->bind_param("i", $_SESSION['id_user']);
            $stmt->execute();
            $result_siswa = $stmt->get_result();
            
            if ($result_siswa->num_rows > 0) {
                $siswa_data = $result_siswa->fetch_assoc();
            }
        } else {
            $update_status = 'error';
            $update_message = 'Gagal memperbarui data. Silakan coba lagi.';
        }
        $stmt_update->close();
    }
}

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
    $stmt_nilai->close();
}

// Ambil data berkas
$berkas_stats = ['diterima' => 0, 'menunggu' => 0, 'ditolak' => 0];
$foto_profil = null;
if ($no_daftar) {
    $sql_berkas = "SELECT status, COUNT(*) as total FROM tb_berkas_pendaftaran WHERE no_daftar = ? GROUP BY status";
    $stmt_berkas = $conn->prepare($sql_berkas);
    $stmt_berkas->bind_param("s", $no_daftar);
    $stmt_berkas->execute();
    $result_berkas = $stmt_berkas->get_result();
    
    while ($row = $result_berkas->fetch_assoc()) {
        $berkas_stats[$row['status']] = $row['total'];
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Siswa - PSB SMAN 6</title>
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
        
        .profile-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            overflow: hidden;
            border: 3px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .profile-info p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-menunggu {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-diterima {
            background: #d4edda;
            color: #155724;
        }
        
        .status-tidak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }
        
        .info-data {
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .edit-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-group input {
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group input:disabled {
            background: #f8f9fa;
            cursor: not-allowed;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        
        .stat-card h4 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
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
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .status-badge {
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                display: inline-block;
            }
            
            .status-menunggu {
                background: #fff3cd;
                color: #856404;
            }
            
            .status-diterima {
                background: #d4edda;
                color: #155724;
            }
            
            .status-tidak {
                background: #f8d7da;
                color: #721c24;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .info-item {
                display: flex;
                flex-direction: column;
            }
            
            .info-label {
                font-weight: 600;
                color: #7f8c8d;
                font-size: 0.85rem;
                margin-bottom: 5px;
            }
            
            .info-data {
                color: #2c3e50;
                font-size: 1rem;
            }
            
            .edit-form {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            
            .form-group {
                display: flex;
                flex-direction: column;
            }
            
            .form-group label {
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 8px;
            }
            
            .form-group input {
                padding: 12px;
                border: 2px solid #e1e8ed;
                border-radius: 6px;
                font-size: 14px;
                transition: border-color 0.3s;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .form-group input:disabled {
                background: #f8f9fa;
                cursor: not-allowed;
            }
            
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                display: inline-block;
                text-align: center;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
            
            .btn-secondary:hover {
                background: #5a6268;
            }
            
            .alert {
                padding: 15px;
                border-radius: 6px;
                margin-bottom: 20px;
                border-left: 4px solid;
            }
            
            .alert-success {
                background: #d4edda;
                border-left-color: #28a745;
                color: #155724;
            }
            
            .alert-error {
                background: #f8d7da;
                border-left-color: #dc3545;
                color: #721c24;
            }
            
            .alert-warning {
                background: #fff3cd;
                border-left-color: #ffc107;
                color: #856404;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 20px;
            }
            
            .stat-card {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                text-align: center;
            }
            
            .stat-card h4 {
                color: #667eea;
                font-size: 1.5rem;
                margin-bottom: 5px;
            }
            
            .stat-card p {
                color: #7f8c8d;
                font-size: 0.9rem;
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
            
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                
                .main-content {
                    margin-left: 0;
                }
                
                .profile-header {
                    flex-direction: column;
                    text-align: center;
                }
                
                .profile-content {
                    grid-template-columns: 1fr;
                }
                
                .form-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Profil Siswa</h2>
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

        <?php if (!$siswa_data): ?>
            <!-- Alert untuk siswa yang belum mendaftar -->
            <div class="alert-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Belum Ada Data Pendaftaran</h4>
                <p>Anda belum melakukan pendaftaran. Silakan hubungi panitia untuk informasi lebih lanjut.</p>
            </div>
        <?php else: ?>
            <!-- Profile Header -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($foto_profil && file_exists($foto_profil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_profil); ?>" alt="Foto Profil">
                        <?php else: ?>
                            <?php echo strtoupper(substr($siswa_data['nama_clnsiswa'], 0, 2)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($siswa_data['nama_clnsiswa']); ?></h2>
                        <p><i class="fas fa-id-card"></i> No. Pendaftaran: <?php echo htmlspecialchars($siswa_data['no_daftar']); ?></p>
                        <p><i class="fas fa-graduation-cap"></i> Jurusan: <?php echo htmlspecialchars($siswa_data['nama_jurusan'] ?? '-'); ?></p>
                        <p><i class="fas fa-school"></i> Sekolah Asal: <?php echo htmlspecialchars($siswa_data['asal_sklh'] ?? '-'); ?></p>
                        <p style="margin-top: 10px;">
                            Status Pendaftaran: 
                            <span class="status-badge status-<?php echo $siswa_data['status']; ?>">
                                <?php echo ucfirst($siswa_data['status']); ?>
                            </span>
                        </p>
                        <?php if (!$foto_profil): ?>
                            <p style="margin-top: 10px; color: #7f8c8d; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> Upload foto di halaman <a href="upload_berkas.php" style="color: #667eea;">Upload Berkas</a> untuk menampilkan foto profil
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4><?php echo $berkas_stats['diterima'] + $berkas_stats['menunggu'] + $berkas_stats['ditolak']; ?></h4>
                        <p>Total Berkas</p>
                    </div>
                    <div class="stat-card">
                        <h4><?php echo $berkas_stats['diterima']; ?></h4>
                        <p>Berkas Diterima</p>
                    </div>
                    <div class="stat-card">
                        <h4><?php echo $berkas_stats['menunggu']; ?></h4>
                        <p>Berkas Menunggu</p>
                    </div>
                    <div class="stat-card">
                        <h4><?php echo $berkas_stats['ditolak']; ?></h4>
                        <p>Berkas Ditolak</p>
                    </div>
                </div>
            </div>

            <!-- Data Pribadi Section -->
            <div class="profile-section">
                <div class="section-title">
                    <h3><i class="fas fa-user"></i> Data Pribadi</h3>
                    <button class="btn btn-primary" onclick="toggleEditForm()">
                        <i class="fas fa-edit"></i> Edit Profil
                    </button>
                </div>

                <?php if ($update_message): ?>
                    <div class="alert alert-<?php echo $update_status; ?>">
                        <i class="fas fa-<?php echo $update_status == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $update_message; ?>
                    </div>
                <?php endif; ?>

                <!-- View Mode -->
                <div id="viewMode" class="info-grid">
                    <div class="info-item">
                        <div class="info-label">NISN</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nisn']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIK</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['nik']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. KK</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['no_kk']); ?></div>
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
                        <div class="info-label">Tinggi Badan</div>
                        <div class="info-data"><?php echo $siswa_data['tinggi_badan'] ? $siswa_data['tinggi_badan'] . ' cm' : '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Berat Badan</div>
                        <div class="info-data"><?php echo $siswa_data['berat_badan'] ? $siswa_data['berat_badan'] . ' kg' : '-'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. HP/WhatsApp</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['no_telphp']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tahun Masuk</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['tahun_masuk']); ?></div>
                    </div>
                </div>

                <!-- Edit Mode -->
                <form id="editMode" method="POST" style="display: none;" class="edit-form">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($siswa_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telphp">No. HP/WhatsApp *</label>
                        <input type="tel" name="no_telphp" id="no_telphp" value="<?php echo htmlspecialchars($siswa_data['no_telphp']); ?>" pattern="[0-9]{10,13}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_ortu">Nama Orang Tua *</label>
                        <input type="text" name="nama_ortu" id="nama_ortu" value="<?php echo htmlspecialchars($siswa_data['nama_ortu']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pekerjaan_ortu">Pekerjaan Orang Tua *</label>
                        <input type="text" name="pekerjaan_ortu" id="pekerjaan_ortu" value="<?php echo htmlspecialchars($siswa_data['pekerjaan_ortu']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat_ortu">Alamat *</label>
                        <input type="text" name="alamat_ortu" id="alamat_ortu" value="<?php echo htmlspecialchars($siswa_data['alamat_ortu']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kelurahan">Kelurahan *</label>
                        <input type="text" name="kelurahan" id="kelurahan" value="<?php echo htmlspecialchars($siswa_data['kelurahan']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kecamatan">Kecamatan *</label>
                        <input type="text" name="kecamatan" id="kecamatan" value="<?php echo htmlspecialchars($siswa_data['kecamatan']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kabupaten">Kabupaten *</label>
                        <input type="text" name="kabupaten" id="kabupaten" value="<?php echo htmlspecialchars($siswa_data['kabupaten']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kota">Kota *</label>
                        <input type="text" name="kota" id="kota" value="<?php echo htmlspecialchars($siswa_data['kota']); ?>" required>
                    </div>
                    
                    <div style="grid-column: 1 / -1; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Data Nilai -->
            <?php if ($nilai_data): ?>
            <div class="profile-section">
                <h3 class="section-title"><i class="fas fa-chart-line"></i> Data Nilai</h3>
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
        <?php endif; ?>
    </div>

    <script>
        function toggleEditForm() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'grid';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'grid';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
