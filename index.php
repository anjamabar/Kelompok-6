<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMA Negeri 1 Tanggetada - Portal Pendidikan</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="components/navbar.js"></script>
    <script src="components/footer.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <custom-navbar></custom-navbar>
    <!-- Official Login Portal -->
    <section id="login" class="bg-white py-8 border-b-2 border-gray-200">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-blue-50 rounded-xl shadow-sm border border-blue-100 overflow-hidden">
                <div class="bg-blue-800 py-4 px-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl md:text-2xl font-bold text-white flex iteclass="h-8 mr-3">
                                <span>PORTAL RESMI LOGIN SMA NEGERI 1 TANGGETADA</span>
                            </h2>
                            <p class="text-blue-100 text-sm mt-1">Dikelola oleh Sekretariat SMA Negeri 1 Tanggetada</p>
                        </div>
                        <div class="hidden md:block bg-yellow-400 text-blue-800 px-3 py-1 rounded text-sm font-bold">
                            2025
                        </div>
                    </div>
                </div>
                <div class="p-6 md:flex">
                    <div class="md:w-1/2 md:pr-8 border-b md:border-b-0 md:border-r border-gray-200 pb-6 md:pb-0">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">MASUK SEBAGAI:</h3>
                        
                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-500 transition-colors cursor-pointer">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i data-feather="user" class="text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">SISWA</h4>
                                        <p class="text-sm text-gray-600">Gunakan NIS dan Password</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-500 transition-colors cursor-pointer">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i data-feather="users" class="text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">GURU & TENAGA KEPENDIDIKAN</h4>
                                        <p class="text-sm text-gray-600">Gunakan NIP dan Password</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-500 transition-colors cursor-pointer">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                                        <i data-feather="user-plus" class="text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">TAMU / ALUMNI</h4>
                                        <p class="text-sm text-gray-600">Hubungi Admin Sekolah</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:w-1/2 md:pl-8 pt-6 md:pt-0">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">FORM LOGIN RESMI</h3>
                        <form id="officialLoginForm" action="proses_login.php" method="POST" class="space-y-4">
                            <div>
                                <label for="officialUsername" class="block text-sm font-medium text-gray-700 mb-1">NIS / NIP</label>
                                <input type="text" id="officialUsername" name="username" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Masukkan NIS (siswa) atau NIP (guru)">
                            </div>
                            
                            <div>
                                <label for="officialPassword" class="block text-sm font-medium text-gray-700 mb-1">PASSWORD</label>
                                <input type="password" id="officialPassword" name="password" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Masukkan password">
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="officialRemember" name="officialRemember" type="checkbox" 
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="officialRemember" class="ml-2 block text-sm text-gray-700">Ingat perangkat ini</label>
                                </div>
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lupa password?</a>
                            </div>
                            

                            <div class="pt-2">
                                <button type="submit" 
                                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center">
                                    <i data-feather="log-in" class="mr-2"></i> MASUK PORTAL
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-6 text-center text-sm text-gray-500">
                            <?php
        if (isset($_GET['error']) && $_GET['error'] == 1) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Login Gagal!</strong> Username atau password salah.
                  </div>';
        }
        ?>
                            <p>Penggunaan sistem ini tunduk pada <a href="#" class="text-blue-600 hover:underline">Peraturan Sekolah</a></p>
                            <p class="mt-1">© 2025 SMA Negeri 1 Tanggetada - Dinas Pendidikan Provinsi Sulawesi Tenggara</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Profile Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                    <img src="http://static.photos/education/1024x576/1" alt="SMA Negeri 1 Tanggetada" class="rounded-lg shadow-xl w-full h-auto">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Profil Lengkap SMA Negeri 1 Tanggetada</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                            <i data-feather="info" class="text-blue-600 mr-2"></i> Identitas Sekolah
                        </h3>
                        <ul class="text-gray-600 space-y-2">
                            <li><span class="font-medium">Nama:</span> SMA Negeri 1 Tanggetada</li>
                            <li><span class="font-medium">NPSN:</span> 40403726</li>
                            <li><span class="font-medium">Status:</span> Negeri</li>
                            <li><span class="font-medium">Alamat:</span> Jl. Pendidikan No. 1, Desa/Kelurahan Palewai, Kec. Tanggetada, Kab. Kolaka, Sulawesi Tenggara</li>
                            <li><span class="font-medium">Email:</span> sma1tanggetada@gmail.com</li>
                        </ul>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-3 flex items-center">
                            <i data-feather="clock" class="text-blue-600 mr-2"></i> Sejarah Singkat
                        </h3>
                        <p class="text-gray-600">
                            Berdiri berdasarkan SK Bupati Nomor 60 Tahun 2006. Mulai menerima siswa pada tahun ajaran 2005/2006 dengan jumlah awal 82 siswa. Pada awalnya menumpang di gedung SMP Negeri 1 Tanggetada, kemudian pindah ke gedung baru pada 2 Januari 2006.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i data-feather="award" class="text-blue-600 mr-2"></i> Akreditasi
                            </h4>
                            <p class="text-gray-600">B (SK: 1359/BAN-SM/SK/2022)</p>
                            <p class="text-sm text-gray-500">Masa berlaku sampai 2027</p>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i data-feather="users" class="text-blue-600 mr-2"></i> Data Warga Sekolah
                            </h4>
                            <p class="text-gray-600">Siswa: 488</p>
                            <p class="text-gray-600">Pendidik & Tenaga Kependidikan: 34</p>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i data-feather="home" class="text-blue-600 mr-2"></i> Sarana Prasarana
                            </h4>
                            <p class="text-gray-600">Luas lahan: 8.690 m²</p>
                            <p class="text-gray-600">Fasilitas lengkap: ruang kelas, lab, olahraga</p>
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i data-feather="activity" class="text-blue-600 mr-2"></i> Ekstrakurikuler
                            </h4>
                            <p class="text-gray-600">Olahraga, seni, organisasi siswa</p>
                        </div>
                    </div>
</div>
            </div>
        </div>
    </section>
    <!-- Vision Mission Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Visi & Misi</h2>
            
            <div class="flex flex-col md:flex-row gap-8">
                <div class="md:w-1/2 bg-white p-8 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i data-feather="eye" class="text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Visi</h3>
                    </div>
                    <p class="text-gray-600">
                        Membentuk peserta didik yang beriman, bertanggung jawab dan bertaqwa kepada Tuhan Yang Maha Esa
                    </p>
                    <p class="text-gray-600 mt-2">
                        Mewujudkan lulusan yang unggul dalam bidang akademik dan non akademik
                    </p>
                    <p class="text-gray-600 mt-2">
                        Mengembangkan karakter peserta didik untuk cinta tanah air
                    </p>
                    <p class="text-gray-600 mt-2">
                        Mengikuti Pendidikan dan atau pelatihan yang relevan dengan mata pelajaran yang diampu untuk dapat meningkatkan kualitas pengetahuan, keterampilan dan wawasan guru agar lebih profesional
                    </p>
                    <p class="text-gray-600 mt-2">
                        Mewujudkan pendidikan yang mengedepankan pembentukan Profil Pelajar Pancasila baik untuk guru maupun siswa
                    </p>
                </div>
                
                <div class="md:w-1/2 bg-white p-8 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i data-feather="target" class="text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Misi</h3>
                    </div>
                    <ul class="text-gray-600 list-disc pl-5 space-y-2">
                        <li>Membentuk peserta didik yang beriman, bertanggung jawab dan bertaqwa kepada Tuhan Yang Maha Esa</li>
                        <li>Mewujudkan lulusan yang unggul dalam bidang akademik dan non akademik</li>
                        <li>Mengembangkan karakter peserta didik untuk cinta tanah air</li>
                        <li>Mengikuti Pendidikan dan atau pelatihan yang relevan dengan mata pelajaran yang diampu untuk dapat meningkatkan kualitas pengetahuan, keterampilan dan wawasan guru agar lebih profesional</li>
                        <li>Mewujudkan pendidikan yang mengedepankan pembentukan Profil Pelajar Pancasila baik untuk guru maupun siswa</li>
                    </ul>
                </div>
            
            <div class="mt-12 bg-white p-8 rounded-xl shadow-md">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i data-feather="star" class="text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Ekstrakurikuler Unggulan</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Olahraga</h4>
                        <p class="text-gray-600">Futsal, Volley, Bulutangkis, Basket</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Seni & Budaya</h4>
                        <p class="text-gray-600">Seni Tari, Perfilman</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Pengembangan Diri</h4>
                        <p class="text-gray-600">Organisasi Siswa, Kegiatan Karakter</p>
                    </div>
                </div>
            </div>
</div>
    </section>
    <!-- Facilities Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Fasilitas Sekolah</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/education/640x360/1" alt="Perpustakaan" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="book-open" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Perpustakaan</h3>
                        </div>
                        <p class="text-gray-600">Ruang baca dengan koleksi buku pelajaran dan referensi untuk mendukung pembelajaran.</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/sport/640x360/3" alt="Lapangan Olahraga" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="activity" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Lapangan Olahraga</h3>
                        </div>
                        <p class="text-gray-600">Area olahraga multifungsi untuk mendukung kegiatan ekstrakurikuler siswa.</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/technology/640x360/5" alt="Lab Komputer" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="monitor" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Lab Komputer</h3>
                        </div>
                        <p class="text-gray-600">Laboratorium komputer dengan perangkat untuk pembelajaran teknologi informasi.</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/science/640x360/2" alt="Laboratorium IPA" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="flask" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Laboratorium IPA</h3>
                        </div>
                        <p class="text-gray-600">Fasilitas praktikum untuk mata pelajaran fisika, kimia, dan biologi.</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/workspace/640x360/4" alt="Ruang Kelas" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="home" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Ruang Kelas</h3>
                        </div>
                        <p class="text-gray-600">Ruang belajar yang nyaman dengan kapasitas sesuai standar pendidikan.</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                    <img src="http://static.photos/people/640x360/6" alt="Aula Serbaguna" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <i data-feather="users" class="text-blue-600 mr-2"></i>
                            <h3 class="font-semibold text-lg text-gray-800">Aula Serbaguna</h3>
                        </div>
                        <p class="text-gray-600">Ruang pertemuan untuk kegiatan sekolah, acara, dan presentasi.</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center">
                <p class="text-gray-600">SMA Negeri 1 Tanggetada memiliki luas lahan ± 8.690 m² dengan fasilitas pendukung pendidikan yang memadai.</p>
            </div>
</div>
    </section>
<custom-footer></custom-footer>
    <script>
        feather.replace();
        
        // document.getElementById('officialLoginForm').addEventListener('submit', function(e) {
        //     e.preventDefault();
        //     // Simulate login process
        //     const username = document.getElementById('officialUsername').value;
        //     const password = document.getElementById('officialPassword').value;
            
        //     if(username && password) {
        //         // Show loading state
        //         const btn = this.querySelector('button[type="submit"]');
        //         const originalText = btn.innerHTML;
        //         btn.innerHTML = '<i data-feather="loader" class="animate-spin mr-2"></i> Memeriksa data...';
        //         feather.replace();
                
        //         // Simulate API call
        //         setTimeout(() => {
        //             btn.innerHTML = originalText;
        //             feather.replace();
        //             alert('Selamat datang di Portal SMA Negeri 1 Tanggetada');
        //         }, 1500);
        //     } else {
        //         alert('Harap lengkapi NIS/NIP dan Password');
        //     }
        // });
        
        // Add click handler for login options
        document.querySelectorAll('#login .bg-white').forEach(option => {
            option.addEventListener('click', function() {
                const type = this.querySelector('h4').textContent;
                const usernameField = document.getElementById('officialUsername');
                
                // Update placeholder based on selection
                if(type.includes('SISWA')) {
                    usernameField.placeholder = 'Masukkan NIS Anda';
                } else if(type.includes('GURU')) {
                    usernameField.placeholder = 'Masukkan NIP Anda';
                }
                
                // Visual feedback
                document.querySelectorAll('#login .bg-white').forEach(el => {
                    el.classList.remove('border-blue-500', 'bg-blue-50');
                });
                this.classList.add('border-blue-500', 'bg-blue-50');
            });
        });
    </script>
<script src="script.js"></script>
</body>
</html>