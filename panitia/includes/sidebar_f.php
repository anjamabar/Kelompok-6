<!-- Sidebar Styles -->
<style>
    .sidebar {
        width: 250px;
        background: #2c3e50;
        color: white;
        min-height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        padding: 20px 0;
        z-index: 1000;
        overflow-y: auto;
    }
    
    .logo {
        text-align: center;
        padding: 20px 0;
        border-bottom: 1px solid #3e4e5e;
        margin-bottom: 20px;
    }
    
    .logo h2 {
        color: #fff;
        font-size: 1.2rem;
        margin: 0;
    }
    
    .logo small {
        color: #b8c7ce;
        font-size: 0.8rem;
    }
    
    .menu {
        padding: 0 15px;
    }
    
    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #b8c7ce;
        text-decoration: none;
        border-radius: 4px;
        margin-bottom: 5px;
        transition: all 0.3s;
    }
    
    .menu-item i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .menu-item:hover, 
    .menu-item.active {
        background: #1e282c;
        color: #fff;
    }
    
    .menu-item.active {
        border-left: 3px solid #3c8dbc;
        padding-left: 12px;
    }
    
    .menu-item.logout {
        color: #f39c12;
    }
    
    .menu-item.logout:hover {
        color: #e67e22;
    }
</style>

<!-- Sidebar Content -->
<div class="sidebar">
    <div class="logo">
        <h2>PSB SMAN6</h2>
        <small>Panel Panitia</small>
    </div>
    <div class="menu">
        <a href="../dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="../pendaftar/index.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'pendaftar.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Data Pendaftar
        </a>
        <a href="../verifikasi_berkas.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'verifikasi_berkas.php' ? 'active' : '' ?>">
            <i class="fas fa-file-contract"></i> Verifikasi Berkas
        </a>
        <a href="../nilai/nilai.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'nilai.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Nilai
        </a>
        <a href="../seleksi/seleksi.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'seleksi.php' ? 'active' : '' ?>">
            <i class="fas fa-bullhorn"></i> seleksi
        </a>
        <a href="../laporan/laporan.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Laporan
        </a>
        
        
        <!-- Master Data Menu -->
        <div style="margin: 15px 0; padding: 10px 15px; background: #1e282c; border-radius: 4px;">
            <div style="color: #b8c7ce; font-size: 0.8rem; margin-bottom: 10px; font-weight: 600;">
                <i class="fas fa-database"></i> MASTER DATA
            </div>
            <a href="../master_data/sekolah.php" class="menu-item" style="margin: 5px 0; padding: 8px 10px; font-size: 0.85rem;">
                <i class="fas fa-school" style="width: 16px; font-size: 0.8rem;"></i> Data Sekolah
            </a>
            <a href="../master_data/jurusan.php" class="menu-item" style="margin: 5px 0; padding: 8px 10px; font-size: 0.85rem;">
                <i class="fas fa-graduation-cap" style="width: 16px; font-size: 0.8rem;"></i> Data Jurusan
            </a>
            <a href="../master_data/matpel.php" class="menu-item" style="margin: 5px 0; padding: 8px 10px; font-size: 0.85rem;">
                <i class="fas fa-book" style="width: 16px; font-size: 0.8rem;"></i> Data Mata Pelajaran
            </a>
        </div>
        
        <a href="../../logout.php" class="menu-item logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
