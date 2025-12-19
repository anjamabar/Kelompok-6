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

// Proses verifikasi berkas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id_berkas = $_POST['id_berkas'];
    $action = $_POST['action'];
    $keterangan = $_POST['keterangan'] ?? '';
    
    if ($action === 'terima') {
        $status = 'diterima';
    } elseif ($action === 'tolak') {
        $status = 'ditolak';
    } else {
        $status = 'menunggu';
    }
    
    $update_sql = "UPDATE tb_berkas_pendaftaran SET status = ?, keterangan = ? WHERE id_berkas = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $keterangan, $id_berkas);
    
    if ($stmt->execute()) {
        $success_message = "Status verifikasi berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui status: " . $conn->error;
    }
    $stmt->close();
}

// Ambil data berkas untuk ditampilkan
$sql = "SELECT bp.*, p.nama_clnsiswa, p.no_daftar 
        FROM tb_berkas_pendaftaran bp 
        LEFT JOIN tb_pendaftar p ON bp.no_daftar = p.no_daftar 
        ORDER BY bp.tanggal_upload DESC";
$result = $conn->query($sql);

// Filter berdasarkan status
$filter_status = $_GET['filter'] ?? 'all';
if ($filter_status !== 'all') {
    $sql = "SELECT bp.*, p.nama_clnsiswa, p.no_daftar 
            FROM tb_berkas_pendaftaran bp 
            LEFT JOIN tb_pendaftar p ON bp.no_daftar = p.no_daftar 
            WHERE bp.status = ? 
            ORDER BY bp.tanggal_upload DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter_status);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Berkas - PSB SMAN6</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        
        .header h2 {
            color: #2c3e50;
            margin: 0;
        }
        
        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }
        
        .filter-btn:hover {
            background: #f8f9fa;
        }
        
        .filter-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .berkas-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .berkas-item {
            padding: 20px;
            border-bottom: 1px solid #f1f2f6;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .berkas-item:last-child {
            border-bottom: none;
        }
        
        .berkas-info {
            flex: 1;
        }
        
        .berkas-info h4 {
            color: #2c3e50;
            margin: 0 0 5px 0;
        }
        
        .berkas-info p {
            color: #7f8c8d;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .berkas-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
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
        
        .berkas-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .file-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .file-preview i {
            color: #3498db;
            font-size: 1.2rem;
        }
        
        .modal-preview {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }
        
        .modal-preview-content {
            position: relative;
            margin: 2% auto;
            padding: 20px;
            width: 90%;
            max-width: 900px;
            background: white;
            border-radius: 8px;
            max-height: 90vh;
            overflow: auto;
        }
        
        .preview-close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #333;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1;
        }
        
        .preview-close:hover {
            color: #000;
        }
        
        .preview-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .preview-content {
            text-align: center;
        }
        
        .preview-content img {
            max-width: 100%;
            max-height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .preview-content iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .file-info h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .file-info p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .berkas-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .berkas-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .modal-preview-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2><i class="fas fa-file-check"></i> Verifikasi Berkas Pendaftar</h2>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="filter-container">
            <div class="filter-buttons">
                <a href="?filter=all" class="filter-btn <?= $filter_status === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> Semua Berkas
                </a>
                <a href="?filter=menunggu" class="filter-btn <?= $filter_status === 'menunggu' ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i> Menunggu Verifikasi
                </a>
                <a href="?filter=diterima" class="filter-btn <?= $filter_status === 'diterima' ? 'active' : '' ?>">
                    <i class="fas fa-check"></i> Diterima
                </a>
                <a href="?filter=ditolak" class="filter-btn <?= $filter_status === 'ditolak' ? 'active' : '' ?>">
                    <i class="fas fa-times"></i> Ditolak
                </a>
            </div>
        </div>

        <div class="berkas-container">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = 'status-' . $row['status'];
                    $jenis_berkas_label = ucfirst($row['jenis_berkas']);
                    
                    echo '<div class="berkas-item">
                            <div class="berkas-info">
                                <h4>' . htmlspecialchars($row['nama_clnsiswa'] ?? 'Tidak diketahui') . ' (No: ' . htmlspecialchars($row['no_daftar']) . ')</h4>
                                <p><strong>Jenis Berkas:</strong> ' . $jenis_berkas_label . '</p>
                                <p><strong>Nama File:</strong> ' . htmlspecialchars($row['nama_file']) . '</p>
                                <p><strong>Tanggal Upload:</strong> ' . date('d/m/Y H:i', strtotime($row['tanggal_upload'])) . '</p>';
                    
                    if (!empty($row['keterangan'])) {
                        echo '<p><strong>Keterangan:</strong> ' . htmlspecialchars($row['keterangan']) . '</p>';
                    }
                    
                    echo '<div class="file-preview">
                            <i class="fas fa-file"></i>
                            <span>' . htmlspecialchars($row['nama_file']) . '</span>
                          </div>
                          </div>
                          <div class="berkas-status ' . $status_class . '">' . $row['status'] . '</div>
                          <div class="berkas-actions">';
                    
                    // Tambahkan tombol lihat berkas
                    $file_path = "../berkas/" . $row['nama_file'];
                    if (file_exists($file_path)) {
                        echo '<button class="btn-view" onclick="showPreview(\'' . htmlspecialchars($row['nama_file']) . '\', \'' . htmlspecialchars($row['jenis_berkas']) . '\', \'' . htmlspecialchars($row['nama_clnsiswa'] ?? 'Tidak diketahui') . '\', \'' . date('d/m/Y H:i', strtotime($row['tanggal_upload'])) . '\')">
                                <i class="fas fa-eye"></i> Lihat
                              </button>';
                    }
                    
                    if ($row['status'] === 'menunggu') {
                        echo '<button class="btn btn-success" onclick="showModal(' . $row['id_berkas'] . ', \'terima\')">
                                <i class="fas fa-check"></i> Terima
                              </button>
                              <button class="btn btn-danger" onclick="showModal(' . $row['id_berkas'] . ', \'tolak\')">
                                <i class="fas fa-times"></i> Tolak
                              </button>';
                    } else {
                        echo '<button class="btn btn-primary" onclick="showModal(' . $row['id_berkas'] . ', \'ubah\')">
                                <i class="fas fa-edit"></i> Ubah Status
                              </button>';
                    }
                    
                    echo '</div>
                          </div>';
                }
            } else {
                echo '<div class="empty-state">
                        <i class="fas fa-file"></i>
                        <h3>Tidak Ada Berkas</h3>
                        <p>Belum ada berkas yang diupload atau tidak ada berkas dengan status yang dipilih.</p>
                      </div>';
            }
            ?>
        </div>
    </div>

    <!-- Modal Verifikasi -->
    <div id="verifikasiModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Verifikasi Berkas</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="verifikasiForm" method="POST">
                <input type="hidden" name="id_berkas" id="id_berkas">
                <input type="hidden" name="action" id="action">
                
                <div class="form-group">
                    <label for="keterangan">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan" placeholder="Masukkan keterangan atau alasan penolakan..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Preview Berkas -->
    <div id="previewModal" class="modal-preview">
        <div class="modal-preview-content">
            <span class="preview-close" onclick="closePreview()">&times;</span>
            <div class="preview-header">
                <h3 id="previewTitle">Detail Berkas</h3>
            </div>
            <div class="file-info">
                <h4>Informasi Berkas</h4>
                <p><strong>Nama Pemilik:</strong> <span id="previewNama"></span></p>
                <p><strong>Jenis Berkas:</strong> <span id="previewJenis"></span></p>
                <p><strong>Tanggal Upload:</strong> <span id="previewTanggal"></span></p>
            </div>
            <div class="preview-content" id="previewContent">
                <!-- Konten preview akan dimuat di sini -->
            </div>
        </div>
    </div>

    <script>
        function showModal(id_berkas, action) {
            document.getElementById('id_berkas').value = id_berkas;
            document.getElementById('action').value = action;
            
            const modalTitle = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            const keteranganField = document.getElementById('keterangan');
            
            if (action === 'terima') {
                modalTitle.textContent = 'Terima Berkas';
                submitBtn.textContent = 'Terima';
                submitBtn.className = 'btn btn-success';
                keteranganField.placeholder = 'Masukkan keterangan (opsional)...';
                keteranganField.required = false;
            } else if (action === 'tolak') {
                modalTitle.textContent = 'Tolak Berkas';
                submitBtn.textContent = 'Tolak';
                submitBtn.className = 'btn btn-danger';
                keteranganField.placeholder = 'Masukkan alasan penolakan...';
                keteranganField.required = true;
            } else {
                modalTitle.textContent = 'Ubah Status Berkas';
                submitBtn.textContent = 'Simpan';
                submitBtn.className = 'btn btn-primary';
                keteranganField.placeholder = 'Masukkan keterangan (opsional)...';
                keteranganField.required = false;
            }
            
            document.getElementById('verifikasiModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('verifikasiModal').style.display = 'none';
            document.getElementById('verifikasiForm').reset();
        }
        
        function showPreview(namaFile, jenisBerkkas, namaPemilik, tanggalUpload) {
            document.getElementById('previewNama').textContent = namaPemilik;
            document.getElementById('previewJenis').textContent = jenisBerkkas.charAt(0).toUpperCase() + jenisBerkkas.slice(1);
            document.getElementById('previewTanggal').textContent = tanggalUpload;
            
            const fileUrl = '../berkas/' + namaFile;
            const previewContent = document.getElementById('previewContent');
            const fileExtension = namaFile.split('.').pop().toLowerCase();
            
            // Kosongkan konten preview
            previewContent.innerHTML = '';
            
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExtension)) {
                // Preview untuk gambar
                const img = document.createElement('img');
                img.src = fileUrl;
                img.alt = 'Preview Berkas';
                img.onerror = function() {
                    previewContent.innerHTML = '<p style="color: #e74c3c;">Gagal memuat gambar. File tidak ditemukan atau rusak.</p>';
                };
                previewContent.appendChild(img);
            } else if (fileExtension === 'pdf') {
                // Preview untuk PDF
                const iframe = document.createElement('iframe');
                iframe.src = fileUrl;
                iframe.title = 'Preview PDF';
                iframe.onerror = function() {
                    previewContent.innerHTML = '<p style="color: #e74c3c;">Gagal memuat PDF. File tidak ditemukan atau rusak.</p>';
                };
                previewContent.appendChild(iframe);
            } else {
                // Untuk file type lain, tampilkan link download
                previewContent.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-file" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 20px;"></i>
                        <h4>Preview Tidak Tersedia</h4>
                        <p style="color: #7f8c8d; margin: 10px 0;">File ini tidak dapat dipreview langsung.</p>
                        <a href="${fileUrl}" target="_blank" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    </div>
                `;
            }
            
            document.getElementById('previewModal').style.display = 'block';
        }
        
        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
            document.getElementById('previewContent').innerHTML = '';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const verifikasiModal = document.getElementById('verifikasiModal');
            const previewModal = document.getElementById('previewModal');
            
            if (event.target === verifikasiModal) {
                closeModal();
            }
            if (event.target === previewModal) {
                closePreview();
            }
        }
        
        // Form validation
        document.getElementById('verifikasiForm').addEventListener('submit', function(e) {
            const action = document.getElementById('action').value;
            const keterangan = document.getElementById('keterangan').value;
            
            if (action === 'tolak' && keterangan.trim() === '') {
                e.preventDefault();
                alert('Alasan penolakan harus diisi!');
                return false;
            }
        });
        
        // Close preview with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closePreview();
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>