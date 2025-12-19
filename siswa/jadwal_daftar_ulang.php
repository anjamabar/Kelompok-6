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

// Ambil data siswa
$siswa_data = null;
$no_daftar = null;

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

// Ambil data jadwal daftar ulang dari database
$jadwal_daftar_ulang = [];
$sql_jadwal = "SELECT * FROM tb_jadwal_daftar_ulang ORDER BY tanggal_mulai ASC";
$result_jadwal = $conn->query($sql_jadwal);

if ($result_jadwal->num_rows > 0) {
    while ($row = $result_jadwal->fetch_assoc()) {
        $jadwal_daftar_ulang[] = $row;
    }
}

// Ambil foto profil untuk ditampilkan di header
$foto_profil = null;
if ($no_daftar) {
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

// Syarat daftar ulang
$syarat_daftar_ulang = [
    'Membawa bukti penerimaan (cetak dari portal)',
    'Membawa fotokopi ijazah/SKL yang sudah dilegalisir',
    'Membawa fotokopi akta kelahiran',
    'Membawa fotokopi kartu keluarga',
    'Membawa pas foto 3x4 (2 lembar) berwarna',
    'Membawa surat keterangan sehat dari dokter',
    'Membawa surat keterangan berkelakuan baik dari sekolah asal',
    'Membawa fotokopi NISN',
    'Mengisi formulir pendaftaran ulang di lokasi',
    'Melakukan pembayaran biaya pendaftaran'
];

// Cek status pendaftaran untuk menentukan jadwal yang relevan
$jadwal_relevan = [];
if ($siswa_data && $siswa_data['status'] == 'diterima') {
    // Jika siswa diterima, tampilkan jadwal yang relevan
    $tanggal_sekarang = date('Y-m-d');
    foreach ($jadwal_daftar_ulang as $jadwal) {
        if ($tanggal_sekarang <= $jadwal['tanggal_selesai']) {
            $jadwal_relevan[] = $jadwal;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Daftar Ulang - PSB SMAN 6</title>
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
        
        .schedule-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .schedule-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .schedule-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .schedule-header {
            position: relative;
            z-index: 1;
        }
        
        .schedule-header h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .schedule-date {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .schedule-date i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: rgba(255,255,255,0.8);
        }
        
        .schedule-duration {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 6px;
            width: fit-content;
        }
        
        .schedule-duration i {
            margin-right: 8px;
            color: rgba(255,255,255,0.8);
        }
        
        .schedule-status {
            margin-top: 10px;
        }
        
        .status-upcoming {
            background: rgba(255,193,7,0.2);
            color: #ffc107;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-ongoing {
            background: rgba(40,167,69,0.2);
            color: #28a745;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            animation: pulse 2s infinite;
        }
        
        .status-ended {
            background: rgba(108,117,125,0.2);
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .schedule-details {
            position: relative;
            z-index: 1;
        }
        
        .schedule-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .schedule-item i {
            margin-right: 10px;
            margin-top: 3px;
            width: 20px;
            text-align: center;
        }
        
        .requirements-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .requirements-list {
            list-style: none;
        }
        
        .requirements-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f1f2f6;
            display: flex;
            align-items: flex-start;
        }
        
        .requirements-list li:last-child {
            border-bottom: none;
        }
        
        .requirements-list li i {
            color: #667eea;
            margin-right: 15px;
            margin-top: 3px;
            width: 20px;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            background: #e3f2fd;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-diterima {
            background: #d4edda;
            color: #155724;
        }
        
        .status-menunggu {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-ditolak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .countdown-timer {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            text-align: center;
        }
        
        .countdown-timer h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .countdown-display {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .countdown-item {
            background: #667eea;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            min-width: 60px;
        }
        
        .countdown-item .number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .countdown-item .label {
            font-size: 0.8rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-data {
            font-weight: 600;
            color: #2c3e50;
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
            <h2>Jadwal Daftar Ulang</h2>
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
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-triangle"></i> Data Pendaftaran Tidak Ditemukan</h4>
                <p>Anda belum terdaftar sebagai calon siswa. Silakan hubungi panitia untuk informasi lebih lanjut.</p>
            </div>
        <?php elseif ($siswa_data['status'] != 'diterima'): ?>
            <div class="alert alert-warning">
                <h4><i class="fas fa-info-circle"></i> Status Pendaftaran: <?php echo ucfirst($siswa_data['status']); ?></h4>
                <p>Jadwal daftar ulang hanya tersedia untuk siswa yang diterima. Silakan tunggu pengumuman hasil seleksi.</p>
                
                <div class="info-grid" style="margin-top: 15px;">
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
                </div>
            </div>
        <?php else: ?>
            <!-- Status Diterima -->
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Selamat! Anda Diterima</h4>
                <p>Selamat atas kelulusan Anda! Berikut adalah jadwal daftar ulang yang harus Anda ikuti.</p>
                
                <div class="info-grid" style="margin-top: 15px;">
                    <div class="info-item">
                        <div class="info-label">No. Pendaftaran</div>
                        <div class="info-data"><?php echo htmlspecialchars($siswa_data['no_daftar']); ?></div>
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

            <!-- Jadwal Daftar Ulang -->
            <div class="schedule-section">
                <h3 class="section-title"><i class="fas fa-calendar-alt"></i> Jadwal Daftar Ulang</h3>
                
                <?php if (!empty($jadwal_relevan)): ?>
                    <?php foreach ($jadwal_relevan as $jadwal): ?>
                        <div class="schedule-card">
                            <div class="schedule-header">
                                <h3>Jadwal Daftar Ulang</h3>
                                <div class="schedule-date">
                                    <i class="fas fa-calendar"></i>
                                    <div style="display: flex; flex-direction: column; gap: 5px;">
                                        <span><strong>Mulai:</strong> <?php echo date('d F Y', strtotime($jadwal['tanggal_mulai'])); ?></span>
                                        <span><strong>Selesai:</strong> <?php echo date('d F Y', strtotime($jadwal['tanggal_selesai'])); ?></span>
                                    </div>
                                </div>
                                <div class="schedule-duration">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        <?php 
                                        $mulai = new DateTime($jadwal['tanggal_mulai']);
                                        $selesai = new DateTime($jadwal['tanggal_selesai']);
                                        $interval = $mulai->diff($selesai);
                                        echo $interval->days + 1 . ' hari';
                                        ?>
                                    </span>
                                </div>
                                <div class="schedule-status">
                                    <?php 
                                    $tanggal_sekarang = date('Y-m-d');
                                    if ($tanggal_sekarang < $jadwal['tanggal_mulai']): ?>
                                        <span class="status-upcoming">
                                            <i class="fas fa-hourglass-start"></i> Akan Datang
                                        </span>
                                    <?php elseif ($tanggal_sekarang <= $jadwal['tanggal_selesai']): ?>
                                        <span class="status-ongoing">
                                            <i class="fas fa-play-circle"></i> Sedang Berlangsung
                                        </span>
                                    <?php else: ?>
                                        <span class="status-ended">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="schedule-details">
                                <div class="schedule-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($jadwal['lokasi']); ?></span>
                                </div>
                                <div class="schedule-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span><?php echo htmlspecialchars($jadwal['keterangan']); ?></span>
                                </div>
                            </div>
                            
                            <?php 
                            $tanggal_sekarang = date('Y-m-d');
                            if ($tanggal_sekarang < $jadwal['tanggal_mulai']): 
                                $waktu_tersisa = strtotime($jadwal['tanggal_mulai']) - strtotime($tanggal_sekarang);
                                $hari_tersisa = floor($waktu_tersisa / (60 * 60 * 24));
                            ?>
                                <div class="countdown-timer">
                                    <h4>Countdown Daftar Ulang</h4>
                                    <div class="countdown-display">
                                        <div class="countdown-item">
                                            <div class="number" id="days-<?php echo $jadwal['id']; ?>"><?php echo $hari_tersisa; ?></div>
                                            <div class="label">Hari</div>
                                        </div>
                                        <div class="countdown-item">
                                            <div class="number" id="hours-<?php echo $jadwal['id']; ?>">00</div>
                                            <div class="label">Jam</div>
                                        </div>
                                        <div class="countdown-item">
                                            <div class="number" id="minutes-<?php echo $jadwal['id']; ?>">00</div>
                                            <div class="label">Menit</div>
                                        </div>
                                        <div class="countdown-item">
                                            <div class="number" id="seconds-<?php echo $jadwal['id']; ?>">00</div>
                                            <div class="label">Detik</div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($tanggal_sekarang <= $jadwal['tanggal_selesai']): ?>
                                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; margin-top: 15px;">
                                    <strong><i class="fas fa-bell"></i> Sedang Berlangsung!</strong><br>
                                    Jadwal daftar ulang sedang berlangsung. Segera datang ke lokasi yang ditentukan.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Tidak Ada Jadwal Aktif</h4>
                        <p>Saat ini tidak ada jadwal daftar ulang yang aktif. Silakan periksa kembali nanti atau hubungi panitia.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Syarat Daftar Ulang -->
            <div class="requirements-section">
                <h3 class="section-title"><i class="fas fa-list-check"></i> Syarat Daftar Ulang</h3>
                <ul class="requirements-list">
                    <?php foreach ($syarat_daftar_ulang as $syarat): ?>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo htmlspecialchars($syarat); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Informasi Penting -->
            <div class="schedule-section">
                <h3 class="section-title"><i class="fas fa-exclamation-triangle"></i> Informasi Penting</h3>
                <div class="alert alert-info">
                    <h4><i class="fas fa-info-circle"></i> Catatan Penting</h4>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Daftar ulang harus dilakukan sesuai jadwal yang ditentukan</li>
                        <li>Membawa semua dokumen asli dan fotokopi</li>
                        <li>Orang tua/wali harus mendampingi saat daftar ulang</li>
                        <li>Biaya pendaftaran dapat dibayarkan di lokasi</li>
                        <li>Jika tidak daftar ulang sesuai jadwal, dianggap mengundurkan diri</li>
                        <li>Hubungi panitia jika ada kendala atau pertanyaan</li>
                    </ul>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <h4><i class="fas fa-phone"></i> Kontak Panitia</h4>
                    <p><strong>Telepon:</strong> (0xx) xxxx-xxxx</p>
                    <p><strong>WhatsApp:</strong> +62 xxx-xxxx-xxxx</p>
                    <p><strong>Email:</strong> psb@sman6.sch.id</p>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script>
        // Countdown timer untuk jadwal daftar ulang
        function updateCountdown(targetDate, elementId) {
            const now = new Date().getTime();
            const target = new Date(targetDate).getTime();
            const distance = target - now;

            if (distance < 0) {
                document.getElementById('days-' + elementId).textContent = '0';
                document.getElementById('hours-' + elementId).textContent = '00';
                document.getElementById('minutes-' + elementId).textContent = '00';
                document.getElementById('seconds-' + elementId).textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days-' + elementId).textContent = days;
            document.getElementById('hours-' + elementId).textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes-' + elementId).textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds-' + elementId).textContent = seconds.toString().padStart(2, '0');
        }

        // Update countdown setiap detik
        <?php if (!empty($jadwal_relevan)): ?>
            <?php foreach ($jadwal_relevan as $jadwal): ?>
                <?php 
                $tanggal_sekarang = date('Y-m-d');
                if ($tanggal_sekarang < $jadwal['tanggal_mulai']): 
                    $elementId = $jadwal['id'];
                ?>
                    setInterval(function() {
                        updateCountdown('<?php echo $jadwal['tanggal_mulai']; ?>', '<?php echo $elementId; ?>');
                    }, 1000);
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
</body>
</html>

<?php
$conn->close();
?>