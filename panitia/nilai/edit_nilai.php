<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
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

// Ambil data nilai berdasarkan ID
if (isset($_GET['id'])) {
    $kd_nilai = $_GET['id'];
    $sql = "SELECT n.*, p.nama_clnsiswa, m.nama_matpel 
            FROM tb_nilai n 
            LEFT JOIN tb_pendaftar p ON n.no_daftar = p.no_daftar 
            LEFT JOIN tb_matpel m ON n.kd_matpel = m.kd_matpel 
            WHERE n.kd_nilai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kd_nilai);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: nilai.php?error=not_found");
        exit();
    }
    
    $nilai = $result->fetch_assoc();
} else {
    header("Location: nilai.php");
    exit();
}

// Ambil data pendaftar untuk dropdown
$pendaftar_sql = "SELECT no_daftar, nama_clnsiswa FROM tb_pendaftar ORDER BY nama_clnsiswa";
$pendaftar_result = $conn->query($pendaftar_sql);

// Ambil data matpel untuk dropdown
$matpel_sql = "SELECT kd_matpel, nama_matpel FROM tb_matpel ORDER BY nama_matpel";
$matpel_result = $conn->query($matpel_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nilai - PSB SMAN6</title>
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
        
        .btn-kembali {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-kembali:hover {
            background: #2980b9;
        }
        
        .btn-kembali i {
            margin-right: 8px;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 1.3rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
            background-color: #fafafa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            background-color: white;
        }
        
        .form-control:read-only {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .btn-submit {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.3s;
            margin-right: 10px;
        }
        
        .btn-submit:hover {
            background: #219a52;
        }
        
        .btn-reset {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .btn-reset:hover {
            background: #7f8c8d;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px ubiquitous solid #Binder
        }
ines
        
       iedz
        
       步行
        @@media (raction
           的生长
           信
            .main-contentibs
                marginrim-left: ​​​​​信
                dewa
                padding营
               提质
           中还
           oyer
           扑
           aku
                padding:亲切
           真
            
           庆
           eny
            exac
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .form-container {
                padding: 15px;
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
            <h2>Edit Data Nilai</h2>
            <a href="../nilai/nilai.php" class="btn-kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'duplicate') {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Siswa sudah memiliki nilai untuk mata pelajaran ini!
                      </div>';
            } elseif ($_GET['error'] == 'failed') {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat menyimpan data!
                      </div>';
            }
        }
        ?>

        <div class="form-container">
            <h3 class="section-title">Edit Data Nilai</h3>
            
            <form action="proses_nilai.php" method="post">
                <input type="hidden" name="aksi" value="edit">
                
                <div class="form-group">
                    <label for="kd_nilai" class="form-label">
                        Kode Nilai
                    </label>
                    <input type="text" id="kd_nilai" name="kd_nilai" class="form-control" 
                           value="<?php echo htmlspecialchars($nilai['kd_nilai']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="no_daftar" class="form-label">
                        Nama Siswa <span class="required">*</span>
                    </label>
                    <select id="no_daftar" name="no_daftar" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php
                        if ($pendaftar_result->num_rows > 0) {
                            while ($pendaftar = $pendaftar_result->fetch_assoc()) {
                                $selected = $pendaftar['no_daftar'] == $nilai['no_daftar'] ? 'selected' : '';
                                echo '<option value="' . $pendaftar['no_daftar'] . '" ' . $selected . '>' 
                                     . htmlspecialchars($pendaftar['nama_clnsiswa']) 
                                     . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kd_matpel" class="form-label">
                        Mata Pelajaran <span class="required">*</span>
                    </label>
                    <select id="kd_matpel" name="kd_matpel" class="form-control" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php
                        if ($matpel_result->num_rows > 0) {
                            while ($matpel = $matpel_result->fetch_assoc()) {
                                $selected = $matpel['kd_matpel'] == $nilai['kd_matpel'] ? 'selected' : '';
                                echo '<option value="' . $matpel['kd_matpel'] . '" ' . $selected . '>' 
                                     . htmlspecialchars($matpel['nama_matpel']) 
                                     . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nilai" class="form-label">
                        Nilai <span class="required">*</span>
                    </label>
                    <input type="number" id="nilai" name="nilai" class="form-control" 
                           value="<?php echo htmlspecialchars($nilai['nilai_us']); ?>" 
                           placeholder="Masukkan nilai (0-100)" min="0" max="100" required>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Data
                    </button>
                    <button type="reset" class="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
