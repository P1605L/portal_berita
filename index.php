<?php
// SESSION START HARUS DI AWAL
session_start();

// Include config.php
include 'config.php';

// FUNGSI GET BERITA - PASTIKAN ADA DI config.php ATAU DI SINI
if (!function_exists('getBerita')) {
    function getBerita($koneksi, $limit = null, $kategori = null, $search = null) {
        $query = "SELECT * FROM berita";
        
        $conditions = [];
        
        if ($kategori) {
            $conditions[] = "kategori = '$kategori'";
        }
        
        if ($search) {
            $conditions[] = "(judul LIKE '%$search%' OR isi LIKE '%$search%')";
        }
        
        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY tanggal_publish DESC";
        
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        
        $result = mysqli_query($koneksi, $query);
        
        if (!$result) {
            return [];
        }
        
        $berita = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $berita[] = $row;
        }
        
        return $berita;
    }
}

// Mendapatkan berita utama (3 terbaru)
$berita_utama = getBerita($koneksi, 3);

// Mendapatkan berita berdasarkan kategori
$berita_nasional = getBerita($koneksi, 3, 'nasional');
$berita_internasional = getBerita($koneksi, 3, 'internasional');
$berita_ekonomi = getBerita($koneksi, 3, 'ekonomi');
$berita_teknologi = getBerita($koneksi, 3, 'teknologi');
$berita_olahraga = getBerita($koneksi, 3, 'olahraga');

// Proses pencarian
$hasil_pencarian = [];
if (isset($_GET['cari']) && isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $hasil_pencarian = getBerita($koneksi, null, null, $keyword);
}

// Cek status login admin
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsPedia - Portal Berita Terkini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variabel CSS */
        :root {
            --primary-color: #1a365d;
            --secondary-color: #2a4a7f;
            --accent-color: #e63946;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 8px;
        }

        /* Reset & Base Styles */
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

        .btn-accent {
            background-color: var(--accent-color);
        }

        .btn-accent:hover {
            background-color: #d32f2f;
        }

        /* Top Bar dengan Login Admin */
        .top-bar {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-bar-info i {
            margin-right: 5px;
        }

        .admin-login {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-login a {
            color: white;
            transition: var(--transition);
            padding: 5px 10px;
            border-radius: 4px;
        }

        .admin-login a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .admin-login .admin-name {
            background-color: var(--accent-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
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

        .nav-menu a:hover:after {
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
        .hero {
            padding: 40px 0;
            background: linear-gradient(to right, #1a365d, #2a4a7f);
            color: white;
            margin-bottom: 30px;
            border-radius: 0 0 var(--radius) var(--radius);
        }

        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero-text {
            flex: 1;
            padding-right: 30px;
            animation: fadeIn 1s ease;
        }

        .hero-text h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .hero-image {
            flex: 1;
            position: relative;
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Main Layout */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
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

        /* Sidebar */
        .sidebar {
            background-color: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }

        .sidebar-news {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .sidebar-news:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .sidebar-news img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius);
        }

        .sidebar-news-content h4 {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .sidebar-news-content .news-meta {
            font-size: 0.7rem;
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

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
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
            .top-bar {
                display: none;
            }
            
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
    </style>
</head>
<body>
    <!-- Top Bar dengan Login Admin -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('l, d F Y'); ?></span>
                <span><i class="fas fa-map-marker-alt"></i> Palu, Indonesia</span>
            </div>
            <div class="admin-login">
                <?php if ($is_admin_logged_in): ?>
                    <span class="admin-name"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_username); ?></span>
                    <a href="admin.php"><i class="fas fa-cog"></i> Dashboard</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <i class="fas fa-newspaper"></i>
                <span>NewsPedia</span>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="active">Beranda</a></li>
                    <li><a href="nasional.php">Nasional</a></li>
                    <li><a href="internasional.php">Internasional</a></li>
                    <li><a href="ekonomi.php">Ekonomi</a></li>
                    <li><a href="teknologi.php">Teknologi</a></li>
                    <li><a href="olahraga.php">Olahraga</a></li>
                </ul>
            </nav>
            
            <form class="search-form" method="GET" action="index.php">
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
            <li><a href="index.php" class="active">Beranda</a></li>
            <li><a href="nasional.php">Nasional</a></li>
            <li><a href="internasional.php">Internasional</a></li>
            <li><a href="ekonomi.php">Ekonomi</a></li>
            <li><a href="teknologi.php">Teknologi</a></li>
            <li><a href="olahraga.php">Olahraga</a></li>
        </ul>
        <div class="admin-login" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: var(--radius);">
            <?php if ($is_admin_logged_in): ?>
                <span style="color: var(--accent-color); font-weight: bold;"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_username); ?></span>
                <a href="admin.php" style="color: var(--primary-color);">Dashboard</a>
                <a href="logout.php" style="color: var(--accent-color);">Logout</a>
            <?php else: ?>
                <a href="login.php" style="color: var(--primary-color);"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
            <?php endif; ?>
        </div>
        <form class="search-form" method="GET" action="index.php" style="margin-top: 20px;">
            <input type="text" name="keyword" placeholder="Cari berita..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
            <button type="submit" name="cari"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="overlay"></div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Dapatkan Berita Terkini dan Terpercaya</h1>
                <p>NewsPedia menyajikan informasi terkini dari dalam dan luar negeri dengan reportase yang mendalam dan aktual.</p>
                <?php if ($is_admin_logged_in): ?>
                    <a href="admin.php" class="btn btn-accent" style="margin-top: 20px; margin-right: 10px;">
                        <i class="fas fa-cog"></i> Kelola Berita
                    </a>
                <?php endif; ?>
                <a href="#berita-terbaru" class="btn" style="margin-top: 20px;">Jelajahi Berita</a>
            </div>
            <div class="hero-image">
                <img src="https://www.lxahub.com/hubfs/Weekly%20Round%20up-1.gif" alt="Hero Image">
            </div>
        </div>
    </section>

    <!-- Breaking News -->
    <div class="breaking-news">
        <div class="container">
            <div class="ticker-container">
                <div class="ticker-label">BREAKING NEWS</div>
                <div class="ticker-content">
                    • Setelah Kalah Memalukan, Real Madrid Akui Masalah di Lini Tengah • Timnas Indonesia Lolos ke Piala Asia • Startup Teknologi Indonesia Raih Pendanaan $100 Juta •
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <?php if (isset($_GET['cari']) && isset($_GET['keyword']) && !empty($_GET['keyword'])): ?>
        <!-- Hasil Pencarian -->
        <div class="search-results">
            <h2 class="section-title" id="berita-terbaru">Hasil Pencarian</h2>
            <p class="search-info">Menampilkan hasil pencarian untuk: <span>"<?php echo htmlspecialchars($_GET['keyword']); ?>"</span></p>
            
            <?php if (!empty($hasil_pencarian)): ?>
            <div class="news-grid">
                <?php foreach ($hasil_pencarian as $berita): ?>
                <div class="news-card">
                    <?php
                    // Tampilkan gambar dari database jika ada
                    $gambar_path = isset($berita['gambar']) ? $berita['gambar'] : '';
                    $img_src = "https://placehold.co/600x400/2a4a7f/ffffff?text=" . urlencode($berita['judul']);
                    
                    if (!empty($gambar_path)) {
                        // Coba beberapa path alternatif
                        if (file_exists($gambar_path)) {
                            $img_src = $gambar_path;
                        } else {
                            // Coba path relatif
                            $filename = basename($gambar_path);
                            $upload_path = 'uploads/' . $filename;
                            if (file_exists($upload_path)) {
                                $img_src = $upload_path;
                            }
                        }
                    }
                    ?>
                    <img src="<?php echo $img_src; ?>" 
                         alt="<?php echo htmlspecialchars($berita['judul']); ?>" 
                         class="news-image"
                         onerror="this.src='https://placehold.co/600x400/2a4a7f/ffffff?text=Image+Error'">
                    
                    <div class="news-content">
                        <span class="news-category"><?php echo isset($berita['kategori']) ? ucfirst($berita['kategori']) : 'Berita'; ?></span>
                        <h3 class="news-title">
                            <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                <?php echo htmlspecialchars($berita['judul']); ?>
                            </a>
                        </h3>
                        <p class="news-excerpt">
                            <?php 
                            $isi = isset($berita['isi']) ? strip_tags($berita['isi']) : '';
                            echo substr($isi, 0, 100) . '...'; 
                            ?>
                        </p>
                        <div class="news-meta">
                            <span><i class="far fa-user"></i> <?php echo htmlspecialchars($berita['penulis']); ?></span>
                            <span><i class="far fa-clock"></i> <?php echo isset($berita['tanggal_publish']) ? date('d M Y', strtotime($berita['tanggal_publish'])) : ''; ?></span>
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

        <!-- Berita Utama -->
        <h2 class="section-title" id="berita-terbaru">Berita Terbaru</h2>
        <div class="main-content">
            <div class="content">
                <div class="news-grid">
                    <?php if (empty($berita_utama)): ?>
                        <div style="text-align: center; padding: 40px 0; color: var(--gray-color);">
                            <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.5;"></i>
                            <p>Tidak ada berita saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($berita_utama as $berita): ?>
                        <div class="news-card">
                            <?php
                            // Tampilkan gambar dari database jika ada
                            $gambar_path = isset($berita['gambar']) ? $berita['gambar'] : '';
                            $img_src = "https://placehold.co/600x400/2a4a7f/ffffff?text=" . urlencode($berita['judul']);
                            
                            if (!empty($gambar_path)) {
                                // Cek file exist dengan berbagai cara
                                if (file_exists($gambar_path)) {
                                    $img_src = $gambar_path;
                                } else {
                                    // Coba dengan nama file saja
                                    $filename = basename($gambar_path);
                                    if (file_exists($filename)) {
                                        $img_src = $filename;
                                    } elseif (file_exists('uploads/' . $filename)) {
                                        $img_src = 'uploads/' . $filename;
                                    }
                                }
                            }
                            ?>
                            
                            <img src="<?php echo $img_src; ?>" 
                                 alt="<?php echo htmlspecialchars($berita['judul']); ?>" 
                                 class="news-image"
                                 onerror="this.onerror=null; this.src='https://placehold.co/600x400/2a4a7f/ffffff?text=Gambar+Tidak+Tersedia';">
                            
                            <div class="news-content">
                                <span class="news-category"><?php echo isset($berita['kategori']) ? ucfirst($berita['kategori']) : 'Berita'; ?></span>
                                <h3 class="news-title">
                                    <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                </h3>
                                <p class="news-excerpt">
                                    <?php 
                                    $isi = isset($berita['isi']) ? strip_tags($berita['isi']) : '';
                                    echo substr($isi, 0, 100) . '...'; 
                                    ?>
                                </p>
                                <div class="news-meta">
                                    <span><i class="far fa-user"></i> <?php echo htmlspecialchars($berita['penulis']); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo isset($berita['tanggal_publish']) ? date('d M Y', strtotime($berita['tanggal_publish'])) : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Berita Nasional</h3>
                    <?php if (empty($berita_nasional)): ?>
                        <p style="color: var(--gray-color); font-size: 0.9rem;">Tidak ada berita nasional.</p>
                    <?php else: ?>
                        <?php foreach ($berita_nasional as $berita): ?>
                        <div class="sidebar-news">
                            <?php
                            // Gambar untuk sidebar
                            $gambar_path = isset($berita['gambar']) ? $berita['gambar'] : '';
                            $img_src = "https://placehold.co/80x80/2a4a7f/ffffff?text=" . urlencode(substr($berita['judul'], 0, 20));
                            
                            if (!empty($gambar_path)) {
                                // Cek file exist
                                $filename = basename($gambar_path);
                                $upload_path = 'uploads/' . $filename;
                                if (file_exists($gambar_path)) {
                                    $img_src = $gambar_path;
                                } elseif (file_exists($upload_path)) {
                                    $img_src = $upload_path;
                                } elseif (file_exists($filename)) {
                                    $img_src = $filename;
                                }
                            }
                            ?>
                            <img src="<?php echo $img_src; ?>" 
                                 alt="<?php echo htmlspecialchars($berita['judul']); ?>"
                                 onerror="this.src='https://placehold.co/80x80/2a4a7f/ffffff?text=<?php echo urlencode(substr($berita['judul'], 0, 20)); ?>'">
                            <div class="sidebar-news-content">
                                <h4>
                                    <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                </h4>
                                <div class="news-meta">
                                    <span><i class="far fa-clock"></i> <?php echo isset($berita['tanggal_publish']) ? date('d M Y', strtotime($berita['tanggal_publish'])) : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Berita Internasional</h3>
                    <?php if (empty($berita_internasional)): ?>
                        <p style="color: var(--gray-color); font-size: 0.9rem;">Tidak ada berita internasional.</p>
                    <?php else: ?>
                        <?php foreach ($berita_internasional as $berita): ?>
                        <div class="sidebar-news">
                            <?php
                            // Gambar untuk sidebar
                            $gambar_path = isset($berita['gambar']) ? $berita['gambar'] : '';
                            $img_src = "https://placehold.co/80x80/3c5c9c/ffffff?text=" . urlencode(substr($berita['judul'], 0, 20));
                            
                            if (!empty($gambar_path)) {
                                // Cek file exist
                                $filename = basename($gambar_path);
                                $upload_path = 'uploads/' . $filename;
                                if (file_exists($gambar_path)) {
                                    $img_src = $gambar_path;
                                } elseif (file_exists($upload_path)) {
                                    $img_src = $upload_path;
                                } elseif (file_exists($filename)) {
                                    $img_src = $filename;
                                }
                            }
                            ?>
                            <img src="<?php echo $img_src; ?>" 
                                 alt="<?php echo htmlspecialchars($berita['judul']); ?>"
                                 onerror="this.src='https://placehold.co/80x80/3c5c9c/ffffff?text=<?php echo urlencode(substr($berita['judul'], 0, 20)); ?>'">
                            <div class="sidebar-news-content">
                                <h4>
                                    <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                </h4>
                                <div class="news-meta">
                                    <span><i class="far fa-clock"></i> <?php echo isset($berita['tanggal_publish']) ? date('d M Y', strtotime($berita['tanggal_publish'])) : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Berita Ekonomi</h3>
                    <?php if (empty($berita_ekonomi)): ?>
                        <p style="color: var(--gray-color); font-size: 0.9rem;">Tidak ada berita ekonomi.</p>
                    <?php else: ?>
                        <?php foreach ($berita_ekonomi as $berita): ?>
                        <div class="sidebar-news">
                            <?php
                            // Gambar untuk sidebar
                            $gambar_path = isset($berita['gambar']) ? $berita['gambar'] : '';
                            $img_src = "https://placehold.co/80x80/4caf50/ffffff?text=" . urlencode(substr($berita['judul'], 0, 20));
                            
                            if (!empty($gambar_path)) {
                                // Cek file exist
                                $filename = basename($gambar_path);
                                $upload_path = 'uploads/' . $filename;
                                if (file_exists($gambar_path)) {
                                    $img_src = $gambar_path;
                                } elseif (file_exists($upload_path)) {
                                    $img_src = $upload_path;
                                } elseif (file_exists($filename)) {
                                    $img_src = $filename;
                                }
                            }
                            ?>
                            <img src="<?php echo $img_src; ?>" 
                                 alt="<?php echo htmlspecialchars($berita['judul']); ?>"
                                 onerror="this.src='https://placehold.co/80x80/4caf50/ffffff?text=<?php echo urlencode(substr($berita['judul'], 0, 20)); ?>'">
                            <div class="sidebar-news-content">
                                <h4>
                                    <a href="detail_berita.php?id=<?php echo $berita['id']; ?>">
                                        <?php echo htmlspecialchars($berita['judul']); ?>
                                    </a>
                                </h4>
                                <div class="news-meta">
                                    <span><i class="far fa-clock"></i> <?php echo isset($berita['tanggal_publish']) ? date('d M Y', strtotime($berita['tanggal_publish'])) : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
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
                <p>&copy; <?php echo date('Y'); ?> NewsPedia. All rights reserved.</p>
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
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                // Jika gambar error, ganti dengan placeholder
                if (!this.hasAttribute('data-error-handled')) {
                    this.setAttribute('data-error-handled', 'true');
                    const text = this.alt || 'Image Not Found';
                    this.src = 'https://placehold.co/600x400/2a4a7f/ffffff?text=' + encodeURIComponent(text.substring(0, 30));
                }
            });
        });
    </script>
</body>
</html>
<?php 
// Tutup koneksi database
if (isset($koneksi)) {
    mysqli_close($koneksi);
}
?>