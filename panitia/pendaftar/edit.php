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

// Ambil data pendaftar berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Query untuk mengambil data pendaftar dengan user
    $sql = "SELECT p.*, s.asal_sklh, s.kec, s.kab_kota, j.nama_jurusan,
                   u.username, u.password, u.nama_user, u.role, u.status as user_status
            FROM tb_pendaftar p 
            LEFT JOIN tb_sekolah s ON p.kd_sklh = s.kd_sklh 
            LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan
            LEFT JOIN tb_user u ON p.id_user = u.id_user
            WHERE p.no_daftar = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
        exit();
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

// Ambil data untuk dropdown
$sekolah_sql = "SELECT * FROM tb_sekolah ORDER BY asal_sklh";
$sekolah_result = $conn->query($sekolah_sql);

$jurusan_sql = "SELECT * FROM tb_jurusan ORDER BY nama_jurusan";
$jurusan_result = $conn->query($jurusan_sql);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_daftar = $_POST['no_daftar'];
    $nama_clnsiswa = $_POST['nama_clnsiswa'];
    $nisn = $_POST['nisn'];
    $nik = $_POST['nik'];
    $no_kk = $_POST['no_kk'];
    $jenis_k = $_POST['jenis_k'];
    $tempat_lhr = $_POST['tempat_lhr'];
    $tgl_lhr = $_POST['tgl_lhr'];
    $anak_ke = $_POST['anak_ke'] ?: 1;
    $tinggi_badan = $_POST['tinggi_badan'] ?: 0;
    $berat_badan = $_POST['berat_badan'] ?: 0;
    $agama = $_POST['agama'];
    $no_telphp = $_POST['no_telphp'];
    $email = $_POST['email'];
    $nama_ortu = $_POST['nama_ortu'];
    $alamat_ortu = $_POST['alamat_ortu'];
    $kelurahan = $_POST['kelurahan'];
    $kecamatan = $_POST['kecamatan'];
    $kabupaten = $_POST['kabupaten'];
    $kota = $_POST['kota'];
    $pekerjaan_ortu = $_POST['pekerjaan_ortu'];
    $tahun_masuk = $_POST['tahun_masuk'];
    $kd_sklh = $_POST['kd_sklh'];
    $kd_jurusan = $_POST['kd_jurusan'];
    $status = $_POST['status'];
    
    // Data user
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_status = $_POST['user_status'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update data pendaftar
        $update_sql = "UPDATE tb_pendaftar SET 
                        nama_clnsiswa = ?, nisn = ?, nik = ?, no_kk = ?, jenis_k = ?, tempat_lhr = ?, 
                        tgl_lhr = ?, anak_ke = ?, tinggi_badan = ?, berat_badan = ?, agama = ?, 
                        no_telphp = ?, email = ?, nama_ortu = ?, alamat_ortu = ?, kelurahan = ?, 
                        kecamatan = ?, kabupaten = ?, kota = ?, pekerjaan_ortu = ?, tahun_masuk = ?, 
                        kd_sklh = ?, kd_jurusan = ?, status = ? 
                        WHERE no_daftar = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssssssssssssssssssssss", 
            $nama_clnsiswa, $nisn, $nik, $no_kk, $jenis_k, $tempat_lhr, $tgl_lhr, $anak_ke,
            $tinggi_badan, $berat_badan, $agama, $no_telphp, $email, $nama_ortu, 
            $alamat_ortu, $kelurahan, $kecamatan, $kabupaten, $kota, $pekerjaan_ortu, 
            $tahun_masuk, $kd_sklh, $kd_jurusan, $status, $no_daftar);
        
        $stmt->execute();
        $stmt->close();
        
        // Update or create user account
        if (!empty($username)) {
            if (!empty($data['id_user']) && $data['id_user'] > 0) {
                // Update existing user
                if (!empty($password)) {
                    $user_sql = "UPDATE tb_user SET username = ?, password = ?, nama_user = ?, status = ? WHERE id_user = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("ssssi", $username, $password, $nama_clnsiswa, $user_status, $data['id_user']);
                } else {
                    $user_sql = "UPDATE tb_user SET username = ?, nama_user = ?, status = ? WHERE id_user = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("sssi", $username, $nama_clnsiswa, $user_status, $data['id_user']);
                }
            } else {
                // Create new user
                $user_password = $password ?: $nisn;
                $user_sql = "INSERT INTO tb_user (username, password, nama_user, role, status) VALUES (?, ?, ?, 'siswa', ?)";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("ssss", $username, $user_password, $nama_clnsiswa, $user_status);
            }
            
            $user_stmt->execute();
            
            // If new user, update id_user in pendaftar table
            if (empty($data['id_user']) || $data['id_user'] == 0) {
                $new_user_id = $conn->insert_id;
                $update_user_sql = "UPDATE tb_pendaftar SET id_user = ? WHERE no_daftar = ?";
                $update_stmt = $conn->prepare($update_user_sql);
                $update_stmt->bind_param("is", $new_user_id, $no_daftar);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            $user_stmt->close();
        }
        
        $conn->commit();
        header("Location: index.php?status=edit_sukses");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal mengupdate data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pendaftar - PSB SMAN6</title>
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
        
        .btn-back {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        .btn-back i {
            margin-right: 5px;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 1.3rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        
        .btn-save {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-save:hover {
            background: #219a52;
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar_f.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Edit Data Pendaftar</h2>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3 class="form-title">Edit Data Calon Siswa</h3>
            
            <form action="edit.php?id=<?php echo $id; ?>" method="post">
                <input type="hidden" name="no_daftar" value="<?php echo htmlspecialchars($data['no_daftar']); ?>">
                
                <!-- Data Pribadi -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-user"></i> Data Pribadi
                    </h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_clnsiswa" class="form-label">Nama Lengkap *</label>
                            <input type="text" id="nama_clnsiswa" name="nama_clnsiswa" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['nama_clnsiswa']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nisn" class="form-label">NISN *</label>
                            <input type="text" id="nisn" name="nisn" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['nisn']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" id="nik" name="nik" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['nik']); ?>" maxlength="16">
                        </div>
                        
                        <div class="form-group">
                            <label for="no_kk" class="form-label">No. KK</label>
                            <input type="text" id="no_kk" name="no_kk" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['no_kk']); ?>" maxlength="16">
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_k" class="form-label">Jenis Kelamin *</label>
                            <select id="jenis_k" name="jenis_k" class="form-select" required>
                                <option value="L" <?php echo $data['jenis_k'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo $data['jenis_k'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tempat_lhr" class="form-label">Tempat Lahir *</label>
                            <input type="text" id="tempat_lhr" name="tempat_lhr" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['tempat_lhr']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tgl_lhr" class="form-label">Tanggal Lahir *</label>
                            <input type="date" id="tgl_lhr" name="tgl_lhr" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['tgl_lhr']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="agama" class="form-label">Agama *</label>
                            <select id="agama" name="agama" class="form-select" required>
                                <option value="">Pilih Agama</option>
                                <option value="Islam" <?php echo $data['agama'] == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                                <option value="Kristen" <?php echo $data['agama'] == 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                                <option value="Katolik" <?php echo $data['agama'] == 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                                <option value="Hindu" <?php echo $data['agama'] == 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                                <option value="Buddha" <?php echo $data['agama'] == 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                                <option value="Konghucu" <?php echo $data['agama'] == 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="no_telphp" class="form-label">No. Telepon/HP *</label>
                            <input type="text" id="no_telphp" name="no_telphp" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['no_telphp']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="anak_ke" class="form-label">Anak Ke</label>
                            <input type="number" id="anak_ke" name="anak_ke" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['anak_ke']); ?>" min="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" id="tinggi_badan" name="tinggi_badan" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['tinggi_badan']); ?>" min="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                            <input type="number" id="berat_badan" name="berat_badan" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['berat_badan']); ?>" min="20">
                        </div>
                    </div>
                </div>
                
                <!-- Data Orang Tua -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-users"></i> Data Orang Tua
                    </h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_ortu" class="form-label">Nama Orang Tua *</label>
                            <input type="text" id="nama_ortu" name="nama_ortu" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['nama_ortu']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="pekerjaan_ortu" class="form-label">Pekerjaan Orang Tua *</label>
                            <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['pekerjaan_ortu']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Data Alamat -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Data Alamat
                    </h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="alamat_ortu" class="form-label">Alamat Lengkap *</label>
                            <textarea id="alamat_ortu" name="alamat_ortu" class="form-control" rows="3" required><?php echo htmlspecialchars($data['alamat_ortu']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="kelurahan" class="form-label">Kelurahan</label>
                            <input type="text" id="kelurahan" name="kelurahan" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['kelurahan']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="kecamatan" class="form-label">Kecamatan</label>
                            <input type="text" id="kecamatan" name="kecamatan" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['kecamatan']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="kabupaten" class="form-label">Kabupaten</label>
                            <input type="text" id="kabupaten" name="kabupaten" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['kabupaten']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="kota" class="form-label">Kota</label>
                            <input type="text" id="kota" name="kota" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['kota']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Akun Login -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-user-lock"></i> Akun Login Siswa
                    </h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['username']); ?>" 
                                   placeholder="Kosongkan jika tidak ingin membuat akun">
                            <small class="text-muted">Username untuk login siswa</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Kosongkan untuk tidak mengubah password</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="user_status" class="form-label">Status Akun</label>
                            <select id="user_status" name="user_status" class="form-select">
                                <option value="">Pilih Status</option>
                                <option value="aktif" <?php echo $data['user_status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo $data['user_status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                            <small class="text-muted">Status login akun siswa</small>
                        </div>
                    </div>
                </div>
                
                <!-- Data Pendidikan -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-graduation-cap"></i> Data Pendidikan
                    </h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tahun_masuk" class="form-label">Tahun Masuk *</label>
                            <input type="text" id="tahun_masuk" name="tahun_masuk" class="form-control" 
                                   value="<?php echo htmlspecialchars($data['tahun_masuk']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="kd_sklh" class="form-label">Asal Sekolah *</label>
                            <select id="kd_sklh" name="kd_sklh" class="form-select" required>
                                <option value="">Pilih Sekolah</option>
                                <?php
                                if ($sekolah_result->num_rows > 0) {
                                    while ($sekolah = $sekolah_result->fetch_assoc()) {
                                        $selected = $data['kd_sklh'] == $sekolah['kd_sklh'] ? 'selected' : '';
                                        echo '<option value="' . $sekolah['kd_sklh'] . '" ' . $selected . '>' . 
                                             htmlspecialchars($sekolah['asal_sklh']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="kd_jurusan" class="form-label">Jurusan yang Dipilih *</label>
                            <select id="kd_jurusan" name="kd_jurusan" class="form-select" required>
                                <option value="">Pilih Jurusan</option>
                                <?php
                                if ($jurusan_result->num_rows > 0) {
                                    while ($jurusan = $jurusan_result->fetch_assoc()) {
                                        $selected = $data['kd_jurusan'] == $jurusan['kd_jurusan'] ? 'selected' : '';
                                        echo '<option value="' . $jurusan['kd_jurusan'] . '" ' . $selected . '>' . 
                                             htmlspecialchars($jurusan['nama_jurusan']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">Status Pendaftaran *</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="menunggu" <?php echo $data['status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="diterima" <?php echo $data['status'] == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                                <option value="tidak" <?php echo $data['status'] == 'tidak' ? 'selected' : ''; ?>>Tidak Diterima</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            // Validasi NISN (numeric only)
            const nisn = document.getElementById('nisn');
            if (nisn.value && !/^\d+$/.test(nisn.value)) {
                isValid = false;
                nisn.style.borderColor = '#e74c3c';
                alert('NISN harus berupa angka!');
            }
            
            // Validasi NIK (numeric only)
            const nik = document.getElementById('nik');
            if (nik.value && !/^\d+$/.test(nik.value)) {
                isValid = false;
                nik.style.borderColor = '#e74c3c';
                alert('NIK harus berupa angka!');
            }
            
            // Validasi email
            const email = document.getElementById('email');
            if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                isValid = false;
                email.style.borderColor = '#e74c3c';
                alert('Format email tidak valid!');
            }
            
            // Validasi password jika diisi
            const password = document.getElementById('password');
            if (password.value && password.value.length < 6) {
                isValid = false;
                password.style.borderColor = '#e74c3c';
                alert('Password minimal 6 karakter!');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon periksa kembali data yang diinput!');
            }
        });
        
        // Clear error styling on input
        document.querySelectorAll('.form-control, .form-select').forEach(function(field) {
            field.addEventListener('input', function() {
                this.style.borderColor = '#ddd';
            });
        });
        
        // Toggle password visibility
        const passwordField = document.getElementById('password');
        const passwordToggle = document.createElement('button');
        passwordToggle.type = 'button';
        passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
        passwordToggle.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; cursor: pointer;';
        passwordToggle.onclick = function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordField.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        };
        
        // Add position relative to password field parent
        passwordField.parentElement.style.position = 'relative';
        passwordField.parentElement.appendChild(passwordToggle);
    </script>
</body>
</html>

<?php
$conn->close();
?>
