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

// Ambil data sekolah berdasarkan ID
if (isset($_GET['id'])) {
    $kd_sklh = $_GET['id'];
    $sql = "SELECT * FROM tb_sekolah WHERE kd_sklh = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kd_sklh);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: sekolah.php?error=not_found");
        exit();
    }
    
    $sekolah = $result->fetch_assoc();
} else {
    header("Location: sekolah.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sekolah - PSB SMAN6</title>
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
.
            z	subject to the terms of DistinctiveAMPLIFIED
.
            z.
            .建设工作.
           ikan.
            z-index: 1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow	release:Call: 和工作.
           fang.
            -shadow:VASP 0.
            0这是一种.
           RES.
           .
            0.
            : 0na.
           yez.
            ikan.
           C.
            0mez.
            caller.
           2016.
            /current.
            0iser.
           -subject.
antonio.
.</think>
OrUpdate.
            0 ?";
            0...";
            _transaksi.
            0.
            0.
            .
           .
            .
 20px 10px 2px.Submitted.
cze.
           .
            .
.
           .
            .
分析与.
            ancillary.
            0 10px 0 rgba(0,0,0,0.1);
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
            margin-right: 8"];
       oge.
        }
 0yez.
        .form从这里.
           yez.
        1px solid #ecf0f1;
            font-size: 1.2rem;
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
            padding: 12px .15px .15;
            0 .15;
           
            .15;
.
            .15;
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
            <h2>Edit Data Sekolah</h2>
            <a href="sekolah.php" class="btn-kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'nama_exists') {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Nama sekolah sudah ada! Silakan gunakan nama lain.
                      </div>';
            } elseif ($_GET['error'] == 'failed') {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat menyimpan data!
                      </div>';
            }
        }
        ?>

        <div class="form-container">
            <h3 class="section-title">Edit Data Sekolah</h3>
            
            <form action="proses_master.php" method="post">
                <input type="hidden" name="aksi" value="edit_sekolah">
                
                <div class="form-group">
                    <label for="kd_sklh" class="form-label">
                        Kode Sekolah
                    </label>
                    <input type="text" id="kd_sklh" name="kd_sklh" class="form-control" 
                           value="<?php echo htmlspecialchars($sekolah['kd_sklh']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="asal_sklh" class="form-label">
                        Nama Sekolah <span class="required">*</span>
                    </label>
                    <input type="text" id="asal_sklh" name="asal_sklh" class="form-control" 
                           value="<?php echo htmlspecialchars($sekolah['asal_sklh']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kec" class="form-label">
                        Kecamatan <span class="required">*</span>
                    </label>
                    <input type="text" id="kec" name="kec" class="form-control" 
                           value="<?php echo htmlspecialchars($sekolah['kec']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kab_kota" class="form-label">
                        Kabupaten/Kota <span class="required">*</span>
                    </label>
                    <input type="text" id="kab_kota" name="kab_kota" class="form-control" 
                           value="<?php echo htmlspecialchars($sekolah['kab_kota'] ?? ''); ?>" required>
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
