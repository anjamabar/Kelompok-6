<?php
// Test database connection and queries
include 'panitia/koneksi.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit();
}

echo "<h3>Testing Tables</h3>";

// Test tb_pendaftar
echo "<h4>tb_pendaftar table:</h4>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tb_pendaftar");
if($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>✓ tb_pendaftar exists with {$row['count']} records</p>";
    
    // Show sample data
    $sample = mysqli_query($conn, "SELECT * FROM tb_pendaftar LIMIT 3");
    echo "<table border='1'><tr><th>no_daftar</th><th>nama_clnsiswa</th><th>status</th></tr>";
    while($row = mysqli_fetch_assoc($sample)) {
        echo "<tr><td>{$row['no_daftar']}</td><td>{$row['nama_clnsiswa']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Error querying tb_pendaftar: " . mysqli_error($conn) . "</p>";
}

// Test tb_jurusan
echo "<h4>tb_jurusan table:</h4>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tb_jurusan");
if($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>✓ tb_jurusan exists with {$row['count']} records</p>";
} else {
    echo "<p style='color: red;'>✗ Error querying tb_jurusan: " . mysqli_error($conn) . "</p>";
}

// Test tb_sekolah
echo "<h4>tb_sekolah table:</h4>";
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tb_sekolah");
if($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>✓ tb_sekolah exists with {$row['count']} records</p>";
} else {
    echo "<p style='color: red;'>✗ Error querying tb_sekolah: " . mysqli_error($conn) . "</p>";
}

// Test the summary query
echo "<h4>Summary Query Test:</h4>";
$stats_query = "SELECT 
                 COUNT(*) as total,
                 SUM(CASE WHEN status = 'diterima' THEN 1 ELSE 0 END) as diterima,
                 SUM(CASE WHEN status = 'tidak' THEN 1 ELSE 0 END) as ditolak,
                 SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu
               FROM tb_pendaftar";

$stats_result = mysqli_query($conn, $stats_query);
if($stats_result) {
    $statistics = mysqli_fetch_assoc($stats_result);
    echo "<p>✓ Summary query successful:</p>";
    echo "<ul>";
    echo "<li>Total: {$statistics['total']}</li>";
    echo "<li>Diterima: {$statistics['diterima']}</li>";
    echo "<li>Ditolak: {$statistics['ditolak']}</li>";
    echo "<li>Menunggu: {$statistics['menunggu']}</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Summary query failed: " . mysqli_error($conn) . "</p>";
}

// Test the pendaftar join query
echo "<h4>Pendaftar Join Query Test:</h4>";
$pendaftar_query = "SELECT p.*, j.nama_jurusan, s.asal_sklh
                    FROM tb_pendaftar p
                    LEFT JOIN tb_jurusan j ON p.kd_jurusan = j.kd_jurusan
                    LEFT JOIN tb_sekolah s ON p.kd_sklh = s.kd_sklh
                    ORDER BY p.no_daftar
                    LIMIT 3";

$pendaftar_result = mysqli_query($conn, $pendaftar_query);
if($pendaftar_result) {
    echo "<p>✓ Join query successful:</p>";
    echo "<table border='1'><tr><th>no_daftar</th><th>nama_clnsiswa</th><th>nama_jurusan</th><th>asal_sklh</th><th>status</th></tr>";
    while($row = mysqli_fetch_assoc($pendaftar_result)) {
        echo "<tr>";
        echo "<td>{$row['no_daftar']}</td>";
        echo "<td>{$row['nama_clnsiswa']}</td>";
        echo "<td>" . ($row['nama_jurusan'] ?: '-') . "</td>";
        echo "<td>" . ($row['asal_sklh'] ?: '-') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ Join query failed: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>
