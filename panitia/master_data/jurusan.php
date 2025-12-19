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

// Ambil data jurusan
$sql = "SELECT * FROM tb_jurusan ORDER BY nama_jurusan";
$result = $conn->query($sql);

// Hapus jurusan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete_sql = "DELETE FROM tb_jurusan WHERE kd_jurusan = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        header("Location: jurusan.php?status=hapus_sukses");
        exit();
    }
}

// Generate kode jurusan otomatis
$kode_sql = "SELECT MAX(kd_jurusan) as max_kode FROM tb_jurusan";
$kode_result = $conn->query($kode_sql);
$max_kode = $kode_result->fetch_assoc();
$next_kode = $max_kode['max_kode'] ? sprintf("JR%03d", (int)substr($max_kode['max_kode'], 2) + 1) : 'JR001';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jurusan - PSB SMAN6</title>
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
        
        .btn-tambah {
            background: #27ae60;
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
        
        .btn-tambah:hover {
            background: #219a52;
        }
        
        .btn-tambah i {
            margin-right: 8px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-container, .table-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
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
            width: 100%;
        }
        
        .btn-submit:hover {
            background: #219a52;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        td {
            font-size: 0.8rem;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-edit, .btn-hapus {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2980b9;
        }
        
        .btn-hapus {
            background: #e74c3c;
            color: white;
        }
        
        .btn-hapus:hover {
            background: #c0392b;
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
        
        .required {
            color: #e74c3c;
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
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
            
            .form-container, .table-container {
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
            <h2>Data Jurusan</h2>
            <a href="#" class="btn-tambah" onclick="document.getElementById('formJurusan').scrollIntoView({behavior: 'smooth'})">
                <i class="fas fa-plus"></i> Tambah Jurusan
            </a>
        </div>

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'hapus_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data jurusan berhasil dihapus!
                      </div>';
            } elseif ($_GET['status'] == 'tambah_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data jurusan berhasil ditambahkan!
                      </div>';
            } elseif ($_GET['status'] == 'edit_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data jurusan berhasil diperbarui!
                      </div>';
            }
        }
        ?>

        <div class="content-grid">
            <!-- Form Input -->
            <div class="form-container" id="formJurusan">
                <h3 class="section-title">Input Data Jurusan</h3>
                
                <form action="proses_master.php" method="post">
                    <input type="hidden" name="aksi" value="tambah_jurusan">
                    
                    <div class="form-group">
                        <label for="kd_jurusan" class="form-label">
                            Kode Jurusan
                        </label>
                        <input type="text" id="kd_jurusan" name="kd_jurusan" class="form-control" 
                               value="<?php echo $next_kode; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_jurusan" class="form-label">
                            Nama Jurusan <span class="required">*</span>
                        </label>
                        <input type="text" id="nama_jurusan" name="nama_jurusan" class="form-control" 
                               placeholder="Masukkan nama jurusan" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Simpan Data Jurusan
                    </button>
                </form>
            </div>

            <!-- Tabel Data -->
            <div class="table-container">
                <h3 class="section-title">Daftar Jurusan</h3>
                
                <?php
                if ($result->num_rows > 0) {
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Jurusan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($row['kd_jurusan']) . '</td>
                                <td>' . htmlspecialchars($row['nama_jurusan']) . '</td>
                                <td>
                                    <div class="action-buttons">
                                       
                                        <a href="jurusan.php?hapus=' . $row['kd_jurusan'] . '" class="btn-hapus" 
                                           onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>';
                    }
                    
                    echo '</tbody>
                          </table>';
                } else {
                    echo '<div style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-graduation-cap" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                            <p>Belum ada data jurusan</p>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
