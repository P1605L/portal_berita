<?php
// SESSION START HARUS DI AWAL
session_start();

// Include config.php
include 'config.php';

// Mendapatkan berita kategori internasional
$berita_internasional = getBerita($koneksi, null, 'internasional');

// Handle pencarian
$hasil_pencarian = [];
if (isset($_GET['cari']) && !empty($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    $hasil_pencarian = getBerita($koneksi, null, 'internasional', $keyword);
}

// Set page title
$page_title = "Berita Internasional - NewsPedia";

// Fungsi getBerita
function getBerita($koneksi, $limit = null, $kategori = null, $search = null) {
    // Pastikan koneksi valid
    if (!$koneksi) {
        return [];
    }
    
    // Bangun query
    $query = "SELECT * FROM berita";
    $conditions = [];
    $params = [];
    
    if ($kategori) {
        $conditions[] = "kategori = ?";
        $params[] = $kategori;
    }
    
    if ($search) {
        $conditions[] = "(judul LIKE ? OR isi LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY tanggal_publish DESC";
    
    if ($limit && is_numeric($limit)) {
        $query .= " LIMIT " . intval($limit);
    }
    
    // Gunakan prepared statement
    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) {
        return [];
    }
    
    // Bind parameters jika ada
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        return [];
    }
    
    $berita = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $berita[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $berita;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS tetap sama seperti sebelumnya */
        :root {
            --primary-color: #0f766e;
            --secondary-color: #14b8a6;
            --accent-color:  #14b8a6;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f4f7fc;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            color: var(--accent-color);
        }

        .nav-menu {
            display: flex;
        }

        .nav-menu li {
            margin: 0 15px;
        }

        .nav-menu a {
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-menu a.active {
            color: var(--accent-color);
        }

        .nav-menu a:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent-color);
            transition: var(--transition);
        }

        .nav-menu a:hover:after, .nav-menu a.active:after {
            width: 100%;
        }

        .nav-menu a:hover {
            color: var(--accent-color);
        }

        .search-form {
            display: flex;
            align-items: center;
            background-color: #f1f3f5;
            border-radius: 50px;
            padding: 8px 15px;
        }

        .search-form input {
            border: none;
            background: transparent;
            outline: none;
            padding: 5px;
            width: 180px;
        }

        .search-form button {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--gray-color);
        }

        .mobile-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Hero Section */
        .category-hero {
            padding: 30px 0;
            background: linear-gradient(to right, #1a365d, #2a4a7f);
            color: white;
            margin-bottom: 30px;
            text-align: center;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .category-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .category-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        /* News Grid */
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--accent-color);
            display: inline-block;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .news-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .news-image {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .news-content {
            padding: 20px;
        }

        .news-category {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        .news-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            transition: var(--transition);
        }

        .news-title:hover {
            color: var(--accent-color);
        }

        .news-excerpt {
            color: var(--gray-color);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: var(--gray-color);
        }

        /* Breaking News Ticker */
        .breaking-news {
            background-color: var(--accent-color);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .ticker-container {
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .ticker-label {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 5px 15px;
            margin-right: 20px;
            font-weight: bold;
            border-radius: 4px;
            white-space: nowrap;
        }

        .ticker-content {
            white-space: nowrap;
            animation: ticker 30s linear infinite;
        }

        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Search Results */
        .search-results {
            margin-bottom: 40px;
        }

        .search-info {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .search-info span {
            font-weight: bold;
            color: var(--accent-color);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin: 0 5px;
            border-radius: 50%;
            background-color: white;
            color: var(--dark-color);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .pagination a:hover, .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* Footer */
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--accent-color);
        }

        .footer-column ul li {
            margin-bottom: 10px;
        }

        .footer-column ul li a {
            transition: var(--transition);
        }

        .footer-column ul li a:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-content {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-text {
                padding-right: 0;
                margin-bottom: 30px;
            }
        }

        @media (max-width: 768px) {
            .nav-menu, .search-form {
                display: none;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .news-grid {
                grid-template-columns: 1fr;
            }
            
            .ticker-label {
                font-size: 0.8rem;
                padding: 5px 10px;
            }
        }

        /* Mobile Menu */
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100vh;
            background-color: white;
            z-index: 1001;
            padding: 50px 20px;
            transition: var(--transition);
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .mobile-nav.active {
            right: 0;
        }

        .mobile-nav ul li {
            margin-bottom: 15px;
        }

        .mobile-nav ul li a {
            font-weight: 500;
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .mobile-nav ul li a.active {
            color: var(--accent-color);
        }

        .close-menu {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-newspaper"></i>
                <span>NewsPedia</span>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="nasional.php">Nasional</a></li>
                    <li><a href="internasional.php" class="active">Internasional</a></li>
                    <li><a href="ekonomi.php">Ekonomi</a></li>
                    <li><a href="teknologi.php">Teknologi</a></li>
                    <li><a href="olahraga.php">Olahraga</a></li>
                </ul>
            </nav>
            
            <form class="search-form" method="GET" action="internasional.php">
                <input type="text" name="keyword" placeholder="Cari berita..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                <button type="submit" name="cari"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="close-menu">
            <i class="fas fa-times"></i>
        </div>
        <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="nasional.php">Nasional</a></li>
            <li><a href="internasional.php" class="active">Internasional</a></li>
            <li><a href="ekonomi.php">Ekonomi</a></li>
            <li><a href="teknologi.php">Teknologi</a></li>
            <li><a href="olahraga.php">Olahraga</a></li>
        </ul>
        <form class="search-form" method="GET" action="internasional.php" style="margin-top: 20px;">
            <input type="text" name="keyword" placeholder="Cari berita..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit" name="cari"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="overlay"></div>

    <!-- Hero Section -->
    <section class="category-hero">
        <div class="container">
            <h1>Berita Internasional</h1>
            <p>Informasi terkini seputar berita dunia, politik global, hubungan internasional, dan peristiwa penting di seluruh dunia</p>
        </div>
    </section>

    <!-- Breaking News -->
    <div class="breaking-news">
        <div class="container">
            <div class="ticker-container">
                <div class="ticker-label">BERITA INTERNASIONAL</div>
                <div class="ticker-content">
                    • Konferensi Perdamaian Dunia Digelar di Jenewa • Amerika Serikat dan Tiongkok Sepakat Lanjutkan Perundingan Dagang • PBB Laporkan Kemajuan dalam Penanganan Perubahan Iklim • Uni Eropa Umumkan Paket Bantuan untuk Ukraina •
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <?php if (isset($_GET['cari']) && !empty($_GET['keyword'])): ?>
        <!-- Hasil Pencarian -->
        <div class="search-results">
            <h2 class="section-title">Hasil Pencarian</h2>
            <p class="search-info">Menampilkan hasil pencarian untuk: <span>"<?php echo htmlspecialchars($_GET['keyword']); ?>"</span></p>
            
            <?php if (!empty($hasil_pencarian)): ?>
            <div class="news-grid">
                <?php foreach ($hasil_pencarian as $berita): ?>
                <div class="news-card">
                    <?php
                    // Handle gambar
                    $gambar_path = $berita['gambar'];
                    
                    // Default ke placeholder
                    $img_src = "https://placehold.co/600x400/3c5c9c/ffffff?text=" . urlencode($berita['judul']);
                    
                    // Cek jika ada gambar di database
                    if (!empty($gambar_path)) {
                        // Cek apakah file ada di server
                        if (file_exists($gambar_path)) {
                            $img_src = $gambar_path;
                        } else {
                            // Coba path alternatif
                            $base_path = $_SERVER['DOCUMENT_ROOT'];
                            $relative_path = str_replace($base_path, '', $gambar_path);
                            if (file_exists($relative_path)) {
                                $img_src = $relative_path;
                            } else {
                                // Coba hanya nama file di folder uploads
                                $filename = basename($gambar_path);
                                $upload_path = 'uploads/' . $filename;
                                if (file_exists($upload_path)) {
                                    $img_src = $upload_path;
                                }
                            }
                        }
                    }
                    ?>
                    <img src="<?php echo $img_src; ?>" 
                         alt="<?php echo htmlspecialchars($berita['judul']); ?>" 
                         class="news-image"
                         onerror="this.src='https://placehold.co/600x400/3c5c9c/ffffff?text=Image+Error'">
                    
                    <div class="news-content">
                        <span class="news-category"><?php echo ucfirst($berita['kategori']); ?></span>
                        <h3 class="news-title">
                            <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                <?php echo htmlspecialchars($berita['judul']); ?>
                            </a>
                        </h3>
                        <p class="news-excerpt">
                            <?php 
                            $isi = strip_tags($berita['isi']);
                            echo substr($isi, 0, 100) . '...'; 
                            ?>
                        </p>
                        <div class="news-meta">
                            <span><i class="far fa-user"></i> <?php echo htmlspecialchars($berita['penulis']); ?></span>
                            <span><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($berita['tanggal_publish'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align: center; color: var(--gray-color); padding: 40px 0;">
                Tidak ditemukan berita dengan kata kunci "<?php echo htmlspecialchars($_GET['keyword']); ?>"
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <h2 class="section-title">Berita Internasional Terkini</h2>
        
        <?php if (!empty($berita_internasional)): ?>
        <div class="news-grid">
            <?php foreach ($berita_internasional as $berita): ?>
            <div class="news-card">
                <?php
                // Handle gambar dari database
                $gambar_path = $berita['gambar'];
                
                // Default ke placeholder
                $img_src = "https://placehold.co/600x400/3c5c9c/ffffff?text=" . urlencode($berita['judul']);
                
                // Cek jika ada gambar di database
                if (!empty($gambar_path)) {
                    // Cek apakah file ada di server
                    if (file_exists($gambar_path)) {
                        $img_src = $gambar_path;
                    } else {
                        // Coba path alternatif
                        $base_path = $_SERVER['DOCUMENT_ROOT'];
                        $relative_path = str_replace($base_path, '', $gambar_path);
                        if (file_exists($relative_path)) {
                            $img_src = $relative_path;
                        } else {
                            // Coba hanya nama file di folder uploads
                            $filename = basename($gambar_path);
                            $upload_path = 'uploads/' . $filename;
                            if (file_exists($upload_path)) {
                                $img_src = $upload_path;
                            }
                        }
                    }
                }
                ?>
                
                <img src="<?php echo $img_src; ?>" 
                     alt="<?php echo htmlspecialchars($berita['judul']); ?>" 
                     class="news-image"
                     onerror="this.onerror=null; this.src='https://placehold.co/600x400/3c5c9c/ffffff?text=Gambar+Error';">
                
                <div class="news-content">
                    <span class="news-category"><?php echo ucfirst($berita['kategori']); ?></span>
                    <h3 class="news-title">
                        <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                            <?php echo htmlspecialchars($berita['judul']); ?>
                        </a>
                    </h3>
                    <p class="news-excerpt">
                        <?php 
                        $isi = strip_tags($berita['isi']);
                        echo substr($isi, 0, 100) . '...'; 
                        ?>
                    </p>
                    <div class="news-meta">
                        <span><i class="far fa-user"></i> <?php echo htmlspecialchars($berita['penulis']); ?></span>
                        <span><i class="far fa-clock"></i> <?php echo date('d M Y', strtotime($berita['tanggal_publish'])); ?></span>
                    </div>
                    <a href="detail_berita.php?id=<?php echo $berita['id']; ?>" class="btn" 
                       style="margin-top: 10px; padding: 8px 15px; font-size: 0.9rem; display: inline-block;">
                        Baca Selengkapnya
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 40px 0; color: var(--gray-color);">
            <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
            <p>Belum ada berita internasional</p>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#"><i class="fas fa-chevron-right"></i></a>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Tentang NewsPedia</h3>
                    <p>NewsPedia adalah portal berita terpercaya yang menyajikan informasi terkini dan aktual dari dalam dan luar negeri dengan reportase yang mendalam.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Kategori Berita</h3>
                    <ul>
                        <li><a href="nasional.php">Nasional</a></li>
                        <li><a href="internasional.php">Internasional</a></li>
                        <li><a href="ekonomi.php">Ekonomi</a></li>
                        <li><a href="teknologi.php">Teknologi</a></li>
                        <li><a href="olahraga.php">Olahraga</a></li>
                        <li><a href="#">Hiburan</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Link Terkait</h3>
                    <ul>
                        <li><a href="#">Redaksi</a></li>
                        <li><a href="#">Pedoman Media</a></li>
                        <li><a href="#">Karir</a></li>
                        <li><a href="#">Iklan</a></li>
                        <li><a href="#">Hubungi Kami</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Berlangganan newsletter kami untuk mendapatkan update berita terbaru langsung ke email Anda.</p>
                    <form>
                        <input type="email" placeholder="Alamat Email" style="padding: 10px; width: 100%; margin-bottom: 10px; border-radius: var(--radius); border: none;">
                        <button type="submit" class="btn" style="width: 100%; background-color: var(--accent-color);">Berlangganan</button>
                    </form>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2023 NewsPedia. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileToggle = document.querySelector('.mobile-toggle');
        const mobileNav = document.querySelector('.mobile-nav');
        const overlay = document.querySelector('.overlay');
        const closeMenu = document.querySelector('.close-menu');
        
        mobileToggle.addEventListener('click', () => {
            mobileNav.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        function closeMobileMenu() {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        closeMenu.addEventListener('click', closeMobileMenu);
        overlay.addEventListener('click', closeMobileMenu);
        
        // Animasi saat scroll
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Terapkan animasi pada kartu berita
        document.querySelectorAll('.news-card').forEach(card => {
            card.style.opacity = 0;
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
        
        // Fungsi untuk menangani error gambar
        document.querySelectorAll('.news-image').forEach(img => {
            img.addEventListener('error', function() {
                // Jika gambar error, ganti dengan placeholder
                if (!this.hasAttribute('data-error-handled')) {
                    this.setAttribute('data-error-handled', 'true');
                    this.src = 'https://placehold.co/600x400/3c5c9c/ffffff?text=Image+Not+Found';
                }
            });
        });
    </script>
</body>
</html>
<?php 
// Tutup koneksi database jika ada
if (isset($koneksi)) {
    mysqli_close($koneksi);
}
?>