<?php
include 'panitia/koneksi.php';
$total_query = 'SELECT COUNT(*) as total FROM tb_pendaftar';
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
echo 'Total pendaftar: ' . $total_row['total'];
?>
