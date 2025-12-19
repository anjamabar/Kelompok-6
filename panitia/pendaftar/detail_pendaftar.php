<?php
session_start();

// Cek apakah user sudah login dan role-nya panitia
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'panitia') {
    die('Akses ditolak');
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

// Ambil data pendaftar
$no_daftar = $conn->real_escape_string($_GET['no_daftar']);

// Query untuk mengambil data pendaftar
$sql = "SELECT p.*, j.nama_jurusan, s.asal_sklh, u.username, u.password, u.status as status_akun 
        FROM tb_pendaftar p 
        LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan 
        LEFT JOIN tb_sekolah s ON p.kd_sklh = s.kd_sklh
        LEFT JOIN tb_user u ON p.id_user = u.id_user
        WHERE p.no_daftar = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $no_daftar);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div style="text-align: center; padding: 20px; color: #dc3545;">
            <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <p>Data pendaftar tidak ditemukan.</p>
          </div>';
    exit();
}

$pendaftar = $result->fetch_assoc();
$jenis_kelamin = $pendaftar['jenis_k'] == 'L' ? 'Laki-laki' : 'Perempuan';
$status_akun = $pendaftar['status_akun'] ?? 'tidak_aktif';
$status_akun_text = $status_akun == 'aktif' ? 'Aktif' : 'Tidak Aktif';

// Ambil data berkas pendaftar yang sudah disetujui
$sql_berkas = "SELECT * FROM tb_berkas_pendaftaran WHERE no_daftar = ? AND status = 'diterima' ORDER BY tanggal_upload DESC";
$stmt_berkas = $conn->prepare($sql_berkas);
$stmt_berkas->bind_param("s", $no_daftar);
$stmt_berkas->execute();
$result_berkas = $stmt_berkas->get_result();
$berkas = [];

while ($row = $result_berkas->fetch_assoc()) {
    $berkas[] = $row;
}

// Fungsi untuk menampilkan nilai dalam format yang rapi
function displayValue($value) {
    return !empty($value) ? htmlspecialchars($value) : '<span style="color: #999;">-</span>';
}
?>

<div class="detail-container">
    <h2 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">
        <i class="fas fa-user-graduate"></i> Detail Data Pendaftar
    </h2>
    
    <!-- Informasi Akun -->
    <div class="detail-section">
        <h3><i class="fas fa-user-shield"></i> Informasi Akun</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Username</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['username'] ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Password</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['password'] ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status Akun</div>
                <div class="detail-value">
                    <span class="status-badge status-<?php echo strtolower($status_akun_text); ?>">
                        <?php echo $status_akun_text; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Pribadi -->
    <div class="detail-section">
        <h3><i class="fas fa-id-card"></i> Data Pribadi</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">No. Pendaftaran</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['no_daftar']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Nama Lengkap</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['nama_clnsiswa']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">NISN</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['nisn']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">NIK</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['nik']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">No. KK</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['no_kk']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Jenis Kelamin</div>
                <div class="detail-value"><?php echo $jenis_kelamin; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tempat, Tanggal Lahir</div>
                <div class="detail-value">
                    <?php 
                    $tgl_lahir = !empty($pendaftar['tgl_lhr']) ? date('d/m/Y', strtotime($pendaftar['tgl_lhr'])) : '';
                    echo displayValue($pendaftar['tempat_lhr'] . ($pendaftar['tempat_lhr'] && $tgl_lahir ? ', ' : '') . $tgl_lahir); 
                    ?>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Agama</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['agama']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Anak Ke-</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['anak_ke']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tinggi Badan</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['tinggi_badan'] ? $pendaftar['tinggi_badan'] . ' cm' : ''); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Berat Badan</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['berat_badan'] ? $pendaftar['berat_badan'] . ' kg' : ''); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Kontak -->
    <div class="detail-section">
        <h3><i class="fas fa-address-book"></i> Kontak</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">No. Telepon/HP</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['no_telphp']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['email']); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Data Orang Tua -->
    <div class="detail-section">
        <h3><i class="fas fa-users"></i> Data Orang Tua</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Nama Orang Tua</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['nama_ortu']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Pekerjaan Orang Tua</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['pekerjaan_ortu']); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Alamat</div>
                <div class="detail-value">
                    <?php 
                    $alamat = [];
                    if (!empty($pendaftar['alamat_ortu'])) $alamat[] = $pendaftar['alamat_ortu'];
                    if (!empty($pendaftar['kelurahan'])) $alamat[] = 'Kel. ' . $pendaftar['kelurahan'];
                    if (!empty($pendaftar['kecamatan'])) $alamat[] = 'Kec. ' . $pendaftar['kecamatan'];
                    if (!empty($pendaftar['kabupaten'])) $alamat[] = 'Kab. ' . $pendaftar['kabupaten'];
                    if (!empty($pendaftar['kota'])) $alamat[] = $pendaftar['kota'];
                    
                    echo !empty($alamat) ? nl2br(htmlspecialchars(implode(",\n", $alamat))) : '-';
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Sekolah -->
    <div class="detail-section">
        <h3><i class="fas fa-school"></i> Data Sekolah</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Asal Sekolah</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['asal_sklh'] ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tahun Masuk</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['tahun_masuk']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Jurusan</div>
                <div class="detail-value"><?php echo displayValue($pendaftar['nama_jurusan'] ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status Pendaftaran</div>
                <div class="detail-value">
                    <span class="status-badge status-<?php echo strtolower($pendaftar['status']); ?>">
                        <?php echo ucfirst($pendaftar['status']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Berkas Pendaftaran -->
    <div class="detail-section">
        <h3><i class="fas fa-file-upload"></i> Berkas Pendaftaran</h3>
        <?php if (!empty($berkas)): ?>
            <div class="berkas-list">
                <?php 
                $jenis_berkas = [
                    'ijazah' => 'Ijazah',
                    'kk' => 'Kartu Keluarga',
                    'akta' => 'Akta Kelahiran',
                    'skhu' => 'SKHU',
                    'foto' => 'Pas Foto'
                ];
                
                foreach ($berkas as $file): 
                    $file_path = '../berkas/' . $file['nama_file'];
                    $file_extension = strtolower(pathinfo($file['nama_file'], PATHINFO_EXTENSION));
                    $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
                ?>
                    <div class="berkas-item">
                        <div class="detail-label"><?php echo $jenis_berkas[$file['jenis_berkas']] ?? ucfirst($file['jenis_berkas']); ?></div>
                        <div class="detail-value">
                            <?php if (file_exists($file_path)): ?>
                                <?php if ($is_image): ?>
                                    <a href="#" onclick="window.open('<?php echo $file_path; ?>', '_blank', 'width=800,height=600')">
                                        <i class="fas fa-image"></i> Lihat Berkas
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $file_path; ?>" target="_blank">
                                        <i class="fas fa-download"></i> Unduh Berkas
                                    </a>
                                <?php endif; ?>
                                <br>
                                <small>Diunggah: <?php echo date('d/m/Y H:i', strtotime($file['tanggal_upload'])); ?></small>
                                <br>
                                <span class="status-badge status-diterima">
                                    <i class="fas fa-check-circle"></i> Diterima
                                </span>
                                <?php if (!empty($file['keterangan'])): ?>
                                    <div class="detail-label" style="margin-top: 5px;">Keterangan:</div>
                                    <div style="font-size: 0.85em; color: #666;"><?php echo nl2br(htmlspecialchars($file['keterangan'])); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #dc3545;">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo ucfirst($jenis_berkas[$file['jenis_berkas']] ?? $file['jenis_berkas']); ?> tidak ditemukan
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">Belum ada berkas yang diunggah.</p>
        <?php endif; ?>
    </div>
    
    <!-- Tombol Aksi -->
    <div style="margin-top: 30px; text-align: right;">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak
        </button>
        <a href="edit.php?id=<?php echo $pendaftar['no_daftar']; ?>" class="btn-edit" style="margin-left: 10px;">
            <i class="fas fa-edit"></i> Edit Data
        </a>
    </div>
</div>

<style>
    .detail-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }
    
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 600;
    }
    
    .status-menunggu {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-diterima {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-ditolak, .status-tidak_aktif {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-aktif {
        background-color: #d4edda;
        color: #155724;
    }
    
    .btn-print, .btn-edit {
        padding: 8px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-print {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-print:hover {
        background-color: #5a6268;
    }
    
    .btn-edit {
        background-color: #007bff;
        color: white;
    }
    
    .btn-edit:hover {
        background-color: #0069d9;
    }
    
    .btn-print i, .btn-edit i {
        margin-right: 5px;
    }
    
    @media print {
        .detail-section {
            page-break-inside: avoid;
        }
        
        .btn-print, .btn-edit {
            display: none;
        }
    }
</style>
