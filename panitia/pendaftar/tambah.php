<?php
session_start();

// Check if user is logged in and has panitia role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'panitia') {
    header("Location: ../../index.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "psb_sman6";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nama = $conn->real_escape_string($_POST['nama']);
    $nisn = $conn->real_escape_string($_POST['nisn']);
    $nik = $conn->real_escape_string($_POST['nik']);
    $no_kk = $conn->real_escape_string($_POST['no_kk']);
    $jenis_k = $conn->real_escape_string($_POST['jenis_k']);
    $tempat_lhr = $conn->real_escape_string($_POST['tempat_lhr']);
    $tgl_lhr = $conn->real_escape_string($_POST['tgl_lhr']);
    $agama = $conn->real_escape_string($_POST['agama']);
    $no_telphp = $conn->real_escape_string($_POST['no_telphp']);
    $email = $conn->real_escape_string($_POST['email']);
    $nama_ortu = $conn->real_escape_string($_POST['nama_ortu']);
    $alamat_ortu = $conn->real_escape_string($_POST['alamat_ortu']);
    $kelurahan = $conn->real_escape_string($_POST['kelurahan']);
    $kecamatan = $conn->real_escape_string($_POST['kecamatan']);
    $kabupaten = $conn->real_escape_string($_POST['kabupaten']);
    $kota = $conn->real_escape_string($_POST['kota']);
    $pekerjaan_ortu = $conn->real_escape_string($_POST['pekerjaan_ortu']);
    $anak_ke = $conn->real_escape_string($_POST['anak_ke']) ?: 1;
    $tinggi_badan = $conn->real_escape_string($_POST['tinggi_badan']) ?: 0;
    $berat_badan = $conn->real_escape_string($_POST['berat_badan']) ?: 0;
    $tahun_masuk = date('Y'); // Current year
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $user_status = $conn->real_escape_string($_POST['user_status']) ?: 'aktif';
    $kd_jurusan = $conn->real_escape_string($_POST['kd_jurusan']);
    
    // Get school data
    $sekolah_query = $conn->query("SELECT kd_sklh FROM tb_sekolah LIMIT 1");
    $sekolah = $sekolah_query->fetch_assoc();
    $kd_sklh = $sekolah['kd_sklh'] ?? 'SKL001';

    // Validate required fields
    if (empty($nama) || empty($nisn) || empty($nik) || empty($no_kk) || empty($username) || empty($password)) {
        $error = "Semua field yang bertanda bintang (*) wajib diisi.";
    } else {
        // Check if username already exists
        $check_username = $conn->query("SELECT id_user FROM tb_user WHERE username = '$username'");
        if ($check_username->num_rows > 0) {
            $error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Check if NISN already exists
            $check_nisn = $conn->query("SELECT no_daftar FROM tb_pendaftar WHERE nisn = '$nisn'");
            if ($check_nisn->num_rows > 0) {
                $error = "NISN sudah terdaftar.";
            } else {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Generate registration number
                    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(no_daftar, 2) AS UNSIGNED)) as max_no FROM tb_pendaftar");
                    $row = $result->fetch_assoc();
                    $next_no = $row['max_no'] + 1;
                    $no_daftar = str_pad($next_no, 3, '0', STR_PAD_LEFT);

                    // Insert into tb_user first without hashing
                    $insert_user = $conn->prepare("INSERT INTO tb_user (username, password, nama_user, role, status) VALUES (?, ?, ?, 'siswa', ?)");
                    $insert_user->bind_param("ssss", $username, $password, $nama, $user_status);
                    $insert_user->execute();
                    $id_user = $conn->insert_id;

                    // Insert into tb_pendaftar with all fields
                    $insert_pendaftar = $conn->prepare("INSERT INTO tb_pendaftar (
                        no_daftar, nama_clnsiswa, nisn, nik, no_kk, jenis_k, tempat_lhr, tgl_lhr, 
                        anak_ke, tinggi_badan, berat_badan, agama, no_telphp, email, nama_ortu, alamat_ortu, 
                        kelurahan, kecamatan, kabupaten, kota, pekerjaan_ortu, tahun_masuk, kd_sklh, kd_jurusan, id_user
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $insert_pendaftar->bind_param(
                        "sssssssssssssssssssssssss",
                        $no_daftar, $nama, $nisn, $nik, $no_kk, $jenis_k, $tempat_lhr, $tgl_lhr,
                        $anak_ke, $tinggi_badan, $berat_badan, $agama, $no_telphp, $email, $nama_ortu, 
                        $alamat_ortu, $kelurahan, $kecamatan, $kabupaten, $kota, $pekerjaan_ortu, 
                        $tahun_masuk, $kd_sklh, $kd_jurusan, $id_user
                    );
                    
                    $insert_pendaftar->execute();

                    // Commit transaction
                    $conn->commit();
                    $success = "Pendaftaran berhasil! Nomor pendaftaran: " . $no_daftar;
                    
                    // Clear form
                    $_POST = array();
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
}

// Get list of majors for dropdown
$jurusan_query = $conn->query("SELECT * FROM tb_jurusan");
$jurusan_list = [];
while ($row = $jurusan_query->fetch_assoc()) {
    $jurusan_list[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pendaftar Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        .form-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .section-title {
            margin-top: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
            color: #007bff;
        }
    </style>
</head>
<body>

    
    <div class="container-fluid">
        <div class="row">
           <?php include '../includes/sidebar_f.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Pendaftar Baru</h1>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <h4 class="section-title">Data Pribadi</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama" class="form-label required">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nisn" class="form-label required">NISN</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nik" class="form-label required">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="no_kk" class="form-label required">No. KK</label>
                                <input type="text" class="form-control" id="no_kk" name="no_kk" value="<?php echo isset($_POST['no_kk']) ? htmlspecialchars($_POST['no_kk']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label required">Jenis Kelamin</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_k" id="laki" value="L" <?php echo (isset($_POST['jenis_k']) && $_POST['jenis_k'] == 'L') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="laki">Laki-laki</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_k" id="perempuan" value="P" <?php echo (isset($_POST['jenis_k']) && $_POST['jenis_k'] == 'P') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="perempuan">Perempuan</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tempat_lhr" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lhr" name="tempat_lhr" value="<?php echo isset($_POST['tempat_lhr']) ? htmlspecialchars($_POST['tempat_lhr']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="tgl_lhr" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tgl_lhr" name="tgl_lhr" value="<?php echo isset($_POST['tgl_lhr']) ? htmlspecialchars($_POST['tgl_lhr']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama">
                                    <option value="">Pilih Agama</option>
                                    <option value="Islam" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                                    <option value="Kristen" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                                    <option value="Katolik" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                                    <option value="Hindu" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                                    <option value="Buddha" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                                    <option value="Konghucu" <?php echo (isset($_POST['agama']) && $_POST['agama'] == 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="no_telphp" class="form-label">No. Telepon/HP</label>
                                <input type="text" class="form-control" id="no_telphp" name="no_telphp" value="<?php echo isset($_POST['no_telphp']) ? htmlspecialchars($_POST['no_telphp']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="anak_ke" class="form-label">Anak Ke</label>
                                <input type="number" class="form-control" id="anak_ke" name="anak_ke" value="<?php echo isset($_POST['anak_ke']) ? htmlspecialchars($_POST['anak_ke']) : '1'; ?>" min="1">
                            </div>
                            <div class="col-md-4">
                                <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" class="form-control" id="tinggi_badan" name="tinggi_badan" value="<?php echo isset($_POST['tinggi_badan']) ? htmlspecialchars($_POST['tinggi_badan']) : ''; ?>" min="100">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                                <input type="number" class="form-control" id="berat_badan" name="berat_badan" value="<?php echo isset($_POST['berat_badan']) ? htmlspecialchars($_POST['berat_badan']) : ''; ?>" min="20">
                            </div>
                        </div>

                        <h4 class="section-title">Data Orang Tua/Wali</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_ortu" class="form-label">Nama Orang Tua/Wali</label>
                                <input type="text" class="form-control" id="nama_ortu" name="nama_ortu" value="<?php echo isset($_POST['nama_ortu']) ? htmlspecialchars($_POST['nama_ortu']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="pekerjaan_ortu" class="form-label">Pekerjaan Orang Tua/Wali</label>
                                <input type="text" class="form-control" id="pekerjaan_ortu" name="pekerjaan_ortu" value="<?php echo isset($_POST['pekerjaan_ortu']) ? htmlspecialchars($_POST['pekerjaan_ortu']) : ''; ?>">
                            </div>
                        </div>

                        <h4 class="section-title">Alamat</h4>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="alamat_ortu" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat_ortu" name="alamat_ortu" rows="2"><?php echo isset($_POST['alamat_ortu']) ? htmlspecialchars($_POST['alamat_ortu']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="kelurahan" class="form-label">Kelurahan</label>
                                <input type="text" class="form-control" id="kelurahan" name="kelurahan" value="<?php echo isset($_POST['kelurahan']) ? htmlspecialchars($_POST['kelurahan']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="kecamatan" class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan" value="<?php echo isset($_POST['kecamatan']) ? htmlspecialchars($_POST['kecamatan']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="kabupaten" class="form-label">Kabupaten</label>
                                <input type="text" class="form-control" id="kabupaten" name="kabupaten" value="<?php echo isset($_POST['kabupaten']) ? htmlspecialchars($_POST['kabupaten']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="kota" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="kota" name="kota" value="<?php echo isset($_POST['kota']) ? htmlspecialchars($_POST['kota']) : ''; ?>">
                            </div>
                        </div>

                        <h4 class="section-title">Akun Login</h4>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="username" class="form-label required">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <small class="form-text text-muted">Gunakan username yang unik dan mudah diingat</small>
                            </div>
                            <div class="col-md-4">
                                <label for="password" class="form-label required">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="col-md-4">
                                <label for="user_status" class="form-label">Status Akun</label>
                                <select class="form-select" id="user_status" name="user_status">
                                    <option value="aktif" <?php echo (isset($_POST['user_status']) && $_POST['user_status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo (isset($_POST['user_status']) && $_POST['user_status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                                <small class="form-text text-muted">Status login akun siswa</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kd_jurusan" class="form-label required">Jurusan</label>
                                <select class="form-select" id="kd_jurusan" name="kd_jurusan" required>
                                    <option value="">Pilih Jurusan</option>
                                    <?php foreach ($jurusan_list as $jurusan): ?>
                                        <option value="<?php echo htmlspecialchars($jurusan['kd_jurusan']); ?>" <?php echo (isset($_POST['kd_jurusan']) && $_POST['kd_jurusan'] == $jurusan['kd_jurusan']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Validate email format
            const emailField = document.getElementById('email');
            if (emailField && emailField.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value)) {
                    isValid = false;
                    emailField.classList.add('is-invalid');
                    const invalidFeedback = emailField.nextElementSibling;
                    if (invalidFeedback && !invalidFeedback.classList.contains('invalid-feedback')) {
                        const newInvalidFeedback = document.createElement('div');
                        newInvalidFeedback.className = 'invalid-feedback';
                        newInvalidFeedback.textContent = 'Format email tidak valid';
                        emailField.parentNode.insertBefore(newInvalidFeedback, emailField.nextSibling);
                    }
                } else {
                    emailField.classList.remove('is-invalid');
                }
            }

            // Validate password length
            const passwordField = document.getElementById('password');
            if (passwordField && passwordField.value && passwordField.value.length < 6) {
                isValid = false;
                passwordField.classList.add('is-invalid');
                const invalidFeedback = passwordField.nextElementSibling;
                if (invalidFeedback && !invalidFeedback.classList.contains('invalid-feedback')) {
                    const newInvalidFeedback = document.createElement('div');
                    newInvalidFeedback.className = 'invalid-feedback';
                    newInvalidFeedback.textContent = 'Password minimal 6 karakter';
                    passwordField.parentNode.insertBefore(newInvalidFeedback, passwordField.nextSibling);
                }
            } else if (passwordField) {
                passwordField.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll to first invalid field
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Remove invalid class when user starts typing
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
</body>
</html>