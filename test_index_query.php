<?php
include 'panitia/koneksi.php';
$sql = 'SELECT p.*, j.nama_jurusan FROM tb_pendaftar p LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan ORDER BY p.no_daftar ASC';
$result = mysqli_query($conn, $sql);
echo 'Jumlah pendaftar: ' . mysqli_num_rows($result) . PHP_EOL;
while($row = mysqli_fetch_assoc($result)) {
    echo 'No: ' . $row['no_daftar'] . ', Nama: ' . $row['nama_clnsiswa'] . ', Jurusan: ' . ($row['nama_jurusan'] ?: '-') . PHP_EOL;
}
?>
