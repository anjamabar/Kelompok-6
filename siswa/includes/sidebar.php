<style>
/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    z-index: 1000;
    box-shadow: 4px 0 20px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo-section {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
}

.logo-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.logo-icon i {
    font-size: 1.5rem;
    color: white;
}

.logo-text h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
}

.tagline {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.7);
    font-weight: 400;
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 15px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.profile-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-right: 12px;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.2);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.profile-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: white;
    margin-bottom: 2px;
}

.user-role {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.7);
}

.sidebar-menu {
    flex: 1;
    padding: 20px 0;
    overflow-y: auto;
}

.menu-section {
    margin-bottom: 25px;
}

.menu-title {
    padding: 0 25px;
    margin-bottom: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    letter-spacing: 1px;
}

.sidebar-menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.sidebar-menu li {
    margin: 0;
}

.menu-item {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    padding: 12px 25px;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
    border: none;
}

.menu-item:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    padding-left: 30px;
}

.menu-item.active {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, transparent 100%);
    color: white;
    border-left: 3px solid #667eea;
}

.menu-item.logout {
    color: #e74c3c;
}

.menu-item.logout:hover {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.menu-item i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
    font-size: 0.9rem;
}

.menu-item span {
    font-size: 0.9rem;
    font-weight: 500;
}

.sidebar-footer {
    padding: 20px 25px;
    border-top: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.1);
}

.footer-info {
    display: flex;
    align-items: center;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.6);
}

.footer-info i {
    margin-right: 8px;
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0;
    }
}
</style>

<script>
// Toggle sidebar untuk mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('active');
    
    // Tambahkan tombol toggle jika belum ada
    if (!document.querySelector('.sidebar-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 10px 12px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
        `;
        
        toggleBtn.onclick = toggleSidebar;
        document.body.appendChild(toggleBtn);
        
        // Tampilkan tombol hanya di mobile
        if (window.innerWidth <= 768) {
            toggleBtn.style.display = 'block';
        }
        
        // Update visibility saat resize
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
                sidebar.classList.remove('active');
            }
        });
    }
}

// Auto-inisialisasi saat load
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 768) {
        toggleSidebar();
    }
});
</script>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="logo-text">
                <h3>PSB SMAN 6</h3>
                <span class="tagline">Sistem Penerimaan Siswa Baru</span>
            </div>
        </div>
        <div class="user-profile">
            <?php 
            // Ambil foto profil dari session jika ada
            $foto_profil = isset($_SESSION['foto_profil']) ? $_SESSION['foto_profil'] : null;
            if ($foto_profil && file_exists($foto_profil)): ?>
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($foto_profil); ?>" alt="Foto Profil">
                </div>
            <?php else: ?>
                <div class="profile-avatar avatar-placeholder">
                    <?php echo strtoupper(substr($_SESSION['nama_user'], 0, 2)); ?>
                </div>
            <?php endif; ?>
            <div class="profile-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama_user']); ?></div>
                <div class="user-role">Siswa</div>
            </div>
        </div>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-title">Menu Utama</div>
            <ul>
                <li>
                    <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="upload_berkas.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'upload_berkas.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-upload"></i>
                        <span>Upload Berkas</span>
                    </a>
                </li>
                <li>
                    <a href="jadwal_daftar_ulang.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'jadwal_daftar_ulang.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Jadwal Daftar Ulang</span>
                    </a>
                </li>
                <li>
                    <a href="profil.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="menu-section">
            <div class="menu-title">Sistem</div>
            <ul>
                <li>
                    <a href="../logout.php" class="menu-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="sidebar-footer">
        <div class="footer-info">
            <i class="fas fa-info-circle"></i>
            <span>Versi 1.0.0</span>
        </div>
    </div>
</div>
