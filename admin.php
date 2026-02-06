<?php
include 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fungsi untuk menambah berita
if (isset($_POST['tambah_berita'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    
    // Upload gambar
    $gambar_name = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_ext = strtolower(pathinfo($gambar_name, PATHINFO_EXTENSION));
    
    // Validasi ekstensi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($gambar_ext, $allowed_ext)) {
        $error_msg = "Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
    } else {
        // Generate nama unik untuk file
        $new_gambar_name = uniqid('berita_', true) . '.' . $gambar_ext;
        $gambar_path = "uploads/" . $new_gambar_name;
        
        // Buat folder uploads jika belum ada
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        if (move_uploaded_file($gambar_tmp, $gambar_path)) {
            $query = "INSERT INTO berita (judul, isi, kategori, gambar, penulis) 
                      VALUES ('$judul', '$isi', '$kategori', '$gambar_path', '$penulis')";
            
            if (mysqli_query($koneksi, $query)) {
                $success_msg = "Berita berhasil ditambahkan!";
            } else {
                $error_msg = "Error: " . mysqli_error($koneksi);
            }
        } else {
            $error_msg = "Gagal mengupload gambar.";
        }
    }
}

// Fungsi untuk menghapus berita
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Ambil path gambar untuk dihapus dari server
    $query_select = "SELECT gambar FROM berita WHERE id = $id";
    $result = mysqli_query($koneksi, $query_select);
    $row = mysqli_fetch_assoc($result);
    
    if ($row && file_exists($row['gambar'])) {
        unlink($row['gambar']);
    }
    
    $query = "DELETE FROM berita WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        $success_msg = "Berita berhasil dihapus!";
    } else {
        $error_msg = "Error: " . mysqli_error($koneksi);
    }
}

// Ambil semua berita untuk ditampilkan
$query = "SELECT * FROM berita ORDER BY tanggal_publish DESC";
$result = mysqli_query($koneksi, $query);
$berita = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - NewsPedia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-menu {
            list-style: none;
        }

        .admin-menu li {
            margin-bottom: 5px;
        }

        .admin-menu a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }

        .admin-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .admin-menu a:hover {
            background-color: var(--secondary-color);
            padding-left: 25px;
        }

        .admin-menu a.active {
            background-color: var(--accent-color);
        }

        .logout-btn {
            margin-top: 20px;
            text-align: center;
            padding: 0 20px;
        }

        .logout-btn a {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--accent-color);
            color: white;
            border-radius: var(--radius);
            text-decoration: none;
            transition: var(--transition);
            width: 100%;
        }

        .logout-btn a:hover {
            background-color: #c1121f;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .admin-title {
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .admin-subtitle {
            font-size: 1.2rem;
            color: var(--gray-color);
            margin-bottom: 20px;
        }

        /* Form Styles */
        .admin-form {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            font-family: inherit;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--accent-color);
        }

        .btn-danger:hover {
            background-color: #c1121f;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Table Styles */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .admin-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .admin-table tr:hover {
            background-color: #f8f9fa;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .action-buttons a {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 3px;
            color: white;
            text-decoration: none;
        }

        .edit-btn {
            background-color: #28a745;
        }

        .edit-btn:hover {
            background-color: #218838;
        }

        .delete-btn {
            background-color: var(--accent-color);
        }

        .delete-btn:hover {
            background-color: #c1121f;
        }

        /* Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--radius);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info-box {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .info-box h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
            }
            
            .admin-menu {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .admin-menu li {
                margin: 5px;
            }
            
            .admin-menu a {
                padding: 10px 15px;
                border-radius: var(--radius);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h2>Admin Panel</h2>
            <ul class="admin-menu">
                <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin.php#tambah-berita"><i class="fas fa-plus-circle"></i> Tambah Berita</a></li>
                <li><a href="statistik.php"><i class="fas fa-chart-bar"></i> Statistik</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <div class="logout-btn">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard Admin</h1>
                <div class="welcome-msg">
                    <i class="fas fa-user-circle"></i> Selamat datang, <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                </div>
            </div>

            <!-- Statistik Ringkas -->
            <div class="info-box">
                <h3>Statistik Ringkas</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <?php
                    // Query statistik
                    $total_berita = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita");
                    $total_berita = mysqli_fetch_assoc($total_berita)['total'];
                    
                    $berita_hari_ini = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita WHERE DATE(tanggal_publish) = CURDATE()");
                    $berita_hari_ini = mysqli_fetch_assoc($berita_hari_ini)['total'];
                    
                    $kategori_stats = mysqli_query($koneksi, "SELECT kategori, COUNT(*) as total FROM berita GROUP BY kategori");
                    ?>
                    
                    <div style="background-color: #e3f2fd; padding: 15px; border-radius: var(--radius);">
                        <h4>Total Berita</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $total_berita; ?></p>
                    </div>
                    
                    <div style="background-color: #e8f5e9; padding: 15px; border-radius: var(--radius);">
                        <h4>Berita Hari Ini</h4>
                        <p style="font-size: 2rem; font-weight: bold; color: #28a745;"><?php echo $berita_hari_ini; ?></p>
                    </div>
                </div>
            </div>

            <!-- Notifikasi -->
            <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error_msg)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
            <?php endif; ?>

            <!-- Form Tambah Berita -->
            <div class="admin-form" id="tambah-berita">
                <h3><i class="fas fa-plus-circle"></i> Tambah Berita Baru</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="judul">Judul Berita</label>
                        <input type="text" id="judul" name="judul" class="form-control" required placeholder="Masukkan judul berita">
                    </div>
                    
                    <div class="form-group">
                        <label for="isi">Isi Berita</label>
                        <textarea id="isi" name="isi" class="form-control" required placeholder="Tulis isi berita di sini..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="nasional">Nasional</option>
                            <option value="internasional">Internasional</option>
                            <option value="ekonomi">Ekonomi</option>
                            <option value="teknologi">Teknologi</option>
                            <option value="olahraga">Olahraga</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="gambar">Gambar</label>
                        <input type="file" id="gambar" name="gambar" class="form-control" required accept="image/*">
                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF | Maks: 2MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="penulis">Penulis</label>
                        <input type="text" id="penulis" name="penulis" class="form-control" required value="<?php echo $_SESSION['admin_username'] ?? ''; ?>" placeholder="Nama penulis">
                    </div>
                    
                    <button type="submit" name="tambah_berita" class="btn">
                        <i class="fas fa-plus"></i> Tambah Berita
                    </button>
                </form>
            </div>

            <!-- Daftar Berita -->
            <h3 class="admin-subtitle"><i class="fas fa-list"></i> Daftar Berita</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($berita) > 0): ?>
                        <?php foreach ($berita as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td style="max-width: 300px; word-wrap: break-word;"><?php echo htmlspecialchars($item['judul']); ?></td>
                            <td><span style="background-color: #e3f2fd; padding: 3px 8px; border-radius: 20px; font-size: 0.9rem;"><?php echo ucfirst($item['kategori']); ?></span></td>
                            <td><?php echo htmlspecialchars($item['penulis']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($item['tanggal_publish'])); ?></td>
                            <td class="action-buttons">
                                <a href="edit_berita.php?id=<?php echo $item['id']; ?>" class="btn edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="admin.php?hapus=<?php echo $item['id']; ?>" class="btn delete-btn" 
                                   onclick="return confirm('Yakin ingin menghapus berita ini?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <i class="fas fa-newspaper" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                                <p style="color: var(--gray-color);">Belum ada berita</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Auto-hide alert messages after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>