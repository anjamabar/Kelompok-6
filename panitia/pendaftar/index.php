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

// Query untuk mengambil data pendaftar (tanpa filter berkas)
$sql = "SELECT p.*, j.nama_jurusan 
        FROM tb_pendaftar p 
        LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan 
        ORDER BY p.no_daftar ASC";
$result = $conn->query($sql);

// Hapus pendaftar
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete_sql = "DELETE FROM tb_pendaftar WHERE no_daftar = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=hapus_sukses");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftar - PSB SMAN6</title>
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 900px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .detail-section {
            margin-bottom: 20px;
        }
        
        .detail-section h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            font-size: 0.9em;
        }
        
        .detail-value {
            margin-top: 3px;
            color: #2c3e50;
            word-break: break-word;
        }
        
        .btn-detail {
            background: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            display: inline-flex;
            align-items: center;
            margin-right: 5px;
            text-decoration: none;
        }
        
        .btn-detail i {
            margin-right: 5px;
        }
        
        .btn-detail:hover {
            background: #2980b9;
        }
        
        .berkas-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .berkas-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            width: calc(33.333% - 10px);
            box-sizing: border-box;
        }
        
        .berkas-item a {
            color: #3498db;
            text-decoration: none;
            word-break: break-all;
        }
        
        .berkas-item a:hover {
            text-decoration: underline;
        }
        
        .btn-tambah i {
            margin-right: 8px;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .table-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 1.2rem;
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
            font-size: 0.9rem;
        }
        
        td {
            font-size: 0.85rem;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-diproses {
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
        
        .status-menunggu {
            background: #e2e3e5;
            color: #383d41;
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
            font-size: 0.8rem;
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
        
        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            width: 250px;
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
            
            .search-box {
                justify-content: center;
            }
            
            .table-container {
                padding: 10px;
            }
            
            th, td {
                padding: 8px;
                font-size: 0.8rem;
            }
            
            .action-buttons {
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
            <h2>Data Pendaftar</h2>
            <div class="search-box">
                <input type="text" class="search-input" placeholder="Cari pendaftar..." id="searchInput">
                <a href="tambah.php" class="btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Pendaftar
                </a>
            </div>
        </div>

        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'hapus_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data pendaftar berhasil dihapus!
                      </div>';
            } elseif ($_GET['status'] == 'tambah_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data pendaftar berhasil ditambahkan!
                      </div>';
            } elseif ($_GET['status'] == 'edit_sukses') {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Data pendaftar berhasil diperbarui!
                      </div>';
            }
        }
        ?>

        <div class="table-container">
            <h3 class="table-title">Daftar Semua Pendaftar</h3>
            
            <?php
            if ($result->num_rows > 0) {
                echo '<table id="pendaftarTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>Tempat Lahir</th>
                                <th>Tanggal Lahir</th>
                                <th>Jenis Kelamin</th>
                                <th>Agama</th>
                                <th>No. Telepon</th>
                                <th>Nama Orang Tua</th>
                                <th>Alamat</th>
                                <th>Tahun Masuk</th>
                                <th>Jurusan</th>
                                <th>Status</th>
                                <th width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    $jenis_kelamin = $row['jenis_k'] == 'L' ? 'Laki-laki' : 'Perempuan';
                    
                    // Status badge styling
                    $status_class = 'status-' . $row['status'];
                    $status_text = ucfirst($row['status']);
                    
                    echo '<tr>
                            <td>' . $no . '</td>
                            <td>' . htmlspecialchars($row['nama_clnsiswa']) . '</td>
                            <td>' . htmlspecialchars($row['nisn']) . '</td>
                            <td>' . htmlspecialchars($row['tempat_lhr']) . '</td>
                            <td>' . date('d-m-Y', strtotime($row['tgl_lhr'])) . '</td>
                            <td>' . $jenis_kelamin . '</td>
                            <td>' . htmlspecialchars($row['agama']) . '</td>
                            <td>' . htmlspecialchars($row['no_telphp']) . '</td>
                            <td>' . htmlspecialchars($row['nama_ortu']) . '</td>
                            <td>' . htmlspecialchars($row['alamat_ortu']) . '</td>
                            <td>' . htmlspecialchars($row['tahun_masuk']) . '</td>
                            <td>' . htmlspecialchars($row['nama_jurusan'] ?? '-') . '</td>
                            <td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" onclick="showDetail(\'' . $row['no_daftar'] . '\')" class="btn-detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="edit.php?id=' . $row['no_daftar'] . '" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?hapus=' . $row['no_daftar'] . '" class="btn-hapus" 
                                       onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>';
                    $no++;
                }
                
                echo '</tbody>
                      </table>';
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                        <p>Belum ada data pendaftar</p>
                        <p style="font-size: 0.9rem; margin-top: 10px;">
                            Silakan tambahkan data pendaftar baru melalui tombol "Tambah Pendaftar"
                        </p>
                      </div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Modal Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detailContent">
                <!-- Detail content will be loaded here via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <div class="spinner"></div>
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById('detailModal');
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName('close')[0];
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Function to show detail modal
        function showDetail(noDaftar) {
            modal.style.display = 'block';
            
            // Load detail content via AJAX
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('detailContent').innerHTML = this.responseText;
                } else if (this.readyState == 4) {
                    document.getElementById('detailContent').innerHTML = 
                        '<div style="text-align: center; padding: 20px; color: #dc3545;">' +
                        '<i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 15px;"></i>' +
                        '<p>Gagal memuat data. Silakan coba lagi.</p>' +
                        '</div>';
                }
            };
            xhr.open('GET', 'detail_pendaftar.php?no_daftar=' + noDaftar, true);
            xhr.send();
        }
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toUpperCase();
            table = document.getElementById('pendaftarTable');
            tr = table.getElementsByTagName('tr');
            
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = 'none';
                td = tr[i].getElementsByTagName('td');
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            break;
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
