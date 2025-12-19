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

// Proses upload file
$upload_message = '';
$upload_status = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['berkas']) && $no_daftar) {
    $jenis_berkas = $_POST['jenis_berkas'];
    $file = $_FILES['berkas'];
    
    // Validasi file
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_size = $file['size'];
    
    if (!in_array($file_extension, $allowed_types)) {
        $upload_status = 'error';
        $upload_message = 'Tipe file tidak diizinkan. Hanya PDF, JPG, JPEG, PNG yang diperbolehkan.';
    } elseif ($file_size > $max_size) {
        $upload_status = 'error';
        $upload_message = 'Ukuran file terlalu besar. Maksimal 2MB.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_status = 'error';
        $upload_message = 'Terjadi kesalahan saat upload file.';
    } else {
        // Buat nama file unik
        $timestamp = time();
        $random_string = substr(md5(uniqid()), 0, 8);
        $new_filename = "{$no_daftar}_{$jenis_berkas}_{$timestamp}_{$random_string}.{$file_extension}";
        
        // Target path
        $target_dir = "../berkas/";
        $target_file = $target_dir . $new_filename;
        
        // Buat directory jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Simpan ke database
            $sql_insert = "INSERT INTO tb_berkas_pendaftaran (no_daftar, jenis_berkas, nama_file, status) 
                          VALUES (?, ?, ?, 'menunggu')";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $no_daftar, $jenis_berkas, $new_filename);
            
            if ($stmt_insert->execute()) {
                $upload_status = 'success';
                $upload_message = "Berkas {$jenis_berkas} berhasil diupload!";
            } else {
                $upload_status = 'error';
                $upload_message = 'Gagal menyimpan data ke database.';
                // Hapus file yang sudah diupload jika database gagal
                unlink($target_file);
            }
            $stmt_insert->close();
        } else {
            $upload_status = 'error';
            $upload_message = 'Gagal mengupload file.';
        }
    }
}

// Proses hapus berkas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus' && $no_daftar) {
    $id_berkas = $_POST['id_berkas'];
    
    // Ambil data berkas untuk hapus file fisik
    $sql_get_file = "SELECT nama_file FROM tb_berkas_pendaftaran WHERE id_berkas = ? AND no_daftar = ?";
    $stmt_get = $conn->prepare($sql_get_file);
    $stmt_get->bind_param("is", $id_berkas, $no_daftar);
    $stmt_get->execute();
    $result_get = $stmt_get->get_result();
    
    if ($result_get->num_rows > 0) {
        $file_data = $result_get->fetch_assoc();
        $file_path = "../berkas/" . $file_data['nama_file'];
        
        // Hapus dari database
        $sql_delete = "DELETE FROM tb_berkas_pendaftaran WHERE id_berkas = ? AND no_daftar = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("is", $id_berkas, $no_daftar);
        
        if ($stmt_delete->execute()) {
            // Hapus file fisik jika ada
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $upload_status = 'success';
            $upload_message = "Berkas berhasil dihapus!";
        } else {
            $upload_status = 'error';
            $upload_message = 'Gagal menghapus berkas dari database.';
        }
        $stmt_delete->close();
    } else {
        $upload_status = 'error';
        $upload_message = 'Berkas tidak ditemukan.';
    }
    $stmt_get->close();
}

// Ambil data berkas yang sudah diupload
$berkas_data = [];
$foto_profil = null;
if ($no_daftar) {
    $sql_berkas = "SELECT * FROM tb_berkas_pendaftaran WHERE no_daftar = ? ORDER BY tanggal_upload DESC";
    $stmt_berkas = $conn->prepare($sql_berkas);
    $stmt_berkas->bind_param("s", $no_daftar);
    $stmt_berkas->execute();
    $result_berkas = $stmt_berkas->get_result();
    
    if ($result_berkas->num_rows > 0) {
        $berkas_data = $result_berkas->fetch_all(MYSQLI_ASSOC);
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

// Definisi jenis berkas
$jenis_berkas_list = [
    'ijazah' => 'Ijazah',
    'kk' => 'Kartu Keluarga',
    'akta' => 'Akta Kelahiran',
    'skhu' => 'SKHU',
    'foto' => 'Foto'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Berkas - PSB SMAN 6</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f6fa;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .logo-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .logo-text h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
        }
        
        .tagline {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            font-weight: 400;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .profile-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 12px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            margin-bottom: 2px;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
        }
        
        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .menu-section {
            margin-bottom: 25px;
        }
        
        .menu-title {
            padding: 0 25px;
            margin-bottom: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1px;
        }
        
        .sidebar-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .menu-item {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            border: none;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }
        
        .menu-item.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, transparent 100%);
            color: white;
            border-left: 3px solid #667eea;
        }
        
        .menu-item.logout {
            color: #e74c3c;
        }
        
        .menu-item.logout:hover {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .menu-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .menu-item span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .sidebar-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }
        
        .footer-info {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
        }
        
        .footer-info i {
            margin-right: 8px;
            font-size: 0.8rem;
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
        
        .upload-section {
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
        }
        
        .upload-form {
            display: grid;
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
        
        .form-group select,
        .form-group input[type="file"] {
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group select:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #667eea;
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
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        }
        
        .berkas-actions {
            display: flex;
            gap: 10px;
            align-items: center;
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
        
        .berkas-list {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .berkas-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .berkas-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }
        
        .berkas-info {
            flex: 1;
        }
        
        .berkas-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 16px;
        }
        
        .berkas-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .upload-form {
                grid-template-columns: 1fr;
            }
            
            .berkas-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .berkas-actions {
                margin-top: 10px;
                width: 100%;
            }
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
            <h2>Upload Berkas Pendaftaran</h2>
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
            <!-- Info Siswa -->
            <div class="upload-section">
                <h3 class="section-title">Informasi Pendaftaran</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong>No. Pendaftaran:</strong><br>
                        <?php echo htmlspecialchars($siswa_data['no_daftar']); ?>
                    </div>
                    <div>
                        <strong>Nama:</strong><br>
                        <?php echo htmlspecialchars($siswa_data['nama_clnsiswa']); ?>
                    </div>
                    <div>
                        <strong>Jurusan:</strong><br>
                        <?php echo htmlspecialchars($siswa_data['nama_jurusan'] ?? '-'); ?>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="upload-section">
                <h3 class="section-title">Upload Berkas Baru</h3>
                
                <?php if ($upload_message): ?>
                    <div class="alert alert-<?php echo $upload_status; ?>">
                        <i class="fas fa-<?php echo $upload_status == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $upload_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="jenis_berkas">Jenis Berkas *</label>
                        <select name="jenis_berkas" id="jenis_berkas" required>
                            <option value="">-- Pilih Jenis Berkas --</option>
                            <?php foreach ($jenis_berkas_list as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="berkas">Pilih File *</label>
                        <input type="file" name="berkas" id="berkas" required accept=".pdf,.jpg,.jpeg,.png">
                        <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                            Format yang diizinkan: PDF, JPG, JPEG, PNG (Maksimal 2MB)
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Berkas
                    </button>
                </form>
            </div>

            <!-- Daftar Berkas -->
            <div class="berkas-list">
                <h3 class="section-title">Berkas yang Telah Diupload</h3>
                
                <?php if (empty($berkas_data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h4>Belum Ada Berkas</h4>
                        <p>Anda belum mengupload berkas apapun.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($berkas_data as $berkas): ?>
                        <div class="berkas-item">
                            <div class="berkas-info">
                                <h4>
                                    <i class="fas fa-file"></i>
                                    <?php echo htmlspecialchars($jenis_berkas_list[$berkas['jenis_berkas']] ?? $berkas['jenis_berkas']); ?>
                                </h4>
                                <p>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($berkas['tanggal_upload'])); ?>
                                    <?php if ($berkas['keterangan']): ?>
                                        | <i class="fas fa-comment"></i> <?php echo htmlspecialchars($berkas['keterangan']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="berkas-actions">
                                <span class="status-badge status-<?php echo $berkas['status']; ?>">
                                    <?php echo ucfirst($berkas['status']); ?>
                                </span>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas ini?')">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id_berkas" value="<?php echo $berkas['id_berkas']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Panduan Upload -->
            <div class="upload-section">
                <h3 class="section-title">Panduan Upload Berkas</h3>
                <ul style="color: #7f8c8d; line-height: 1.8; padding-left: 20px;">
                    <li>Pastikan file yang diupload dalam format PDF, JPG, JPEG, atau PNG</li>
                    <li>Ukuran file maksimal 2MB</li>
                    <li>File ijazah, KK, akta, dan SKHU disarankan dalam format PDF</li>
                    <li>Foto disarankan dalam format JPG atau JPEG dengan ukuran yang jelas</li>
                    <li>Pastikan semua dokumen terbaca dengan jelas dan tidak blur</li>
                    <li>Setiap jenis berkas hanya bisa diupload satu kali</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>