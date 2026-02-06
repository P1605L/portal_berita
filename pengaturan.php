<?php
include 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? 1;
$success_msg = '';
$error_msg = '';

// Ambil data admin saat ini
$query = "SELECT * FROM admin WHERE id = $admin_id";
$result = mysqli_query($koneksi, $query);
$admin_data = mysqli_fetch_assoc($result);

// Update profil admin
if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $current_password = mysqli_real_escape_string($koneksi, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($koneksi, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($koneksi, $_POST['confirm_password']);
    
    // Validasi username
    if (empty($username)) {
        $error_msg = "Username tidak boleh kosong.";
    } else {
        // Cek jika username sudah digunakan oleh admin lain
        $check_query = "SELECT id FROM admin WHERE username = '$username' AND id != $admin_id";
        $check_result = mysqli_query($koneksi, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_msg = "Username sudah digunakan.";
        } else {
            // Update password jika diisi
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                // Verifikasi password saat ini
                $current_password_md5 = md5($current_password);
                if ($current_password_md5 !== $admin_data['password']) {
                    $error_msg = "Password saat ini salah.";
                } elseif ($new_password !== $confirm_password) {
                    $error_msg = "Password baru tidak cocok.";
                } elseif (strlen($new_password) < 6) {
                    $error_msg = "Password baru minimal 6 karakter.";
                } else {
                    // Update dengan password baru
                    $new_password_md5 = md5($new_password);
                    $update_query = "UPDATE admin SET username = '$username', password = '$new_password_md5' WHERE id = $admin_id";
                }
            } else {
                // Update hanya username
                $update_query = "UPDATE admin SET username = '$username' WHERE id = $admin_id";
            }
            
            if (!isset($error_msg) || empty($error_msg)) {
                if (mysqli_query($koneksi, $update_query)) {
                    $success_msg = "Profil berhasil diperbarui!";
                    $_SESSION['admin_username'] = $username;
                    // Refresh data admin
                    $admin_data['username'] = $username;
                } else {
                    $error_msg = "Error: " . mysqli_error($koneksi);
                }
            }
        }
    }
}

// Update pengaturan sistem
if (isset($_POST['update_settings'])) {
    $site_title = mysqli_real_escape_string($koneksi, $_POST['site_title'] ?? '');
    $site_description = mysqli_real_escape_string($koneksi, $_POST['site_description'] ?? '');
    $items_per_page = intval($_POST['items_per_page'] ?? 10);
    
    // Simpan pengaturan di session atau database
    $_SESSION['site_settings'] = [
        'site_title' => $site_title,
        'site_description' => $site_description,
        'items_per_page' => $items_per_page
    ];
    
    $success_msg = "Pengaturan berhasil disimpan!";
}

// Reset statistik (opsional)
if (isset($_POST['reset_stats'])) {
    // Hanya contoh - implementasi tergantung kebutuhan
    $success_msg = "Statistik berhasil direset!";
}

// Backup database
if (isset($_POST['backup_database'])) {
    $backup_file = 'backup/portal_berita_' . date('Y-m-d_H-i-s') . '.sql';
    
    if (!is_dir('backup')) {
        mkdir('backup', 0777, true);
    }
    
    // Simpan query untuk backup sederhana
    $tables = ['berita', 'admin'];
    $backup_content = "";
    
    foreach ($tables as $table) {
        // DROP TABLE
        $backup_content .= "DROP TABLE IF EXISTS `$table`;\n\n";
        
        // CREATE TABLE
        $create_table = mysqli_query($koneksi, "SHOW CREATE TABLE `$table`");
        $row = mysqli_fetch_row($create_table);
        $backup_content .= $row[1] . ";\n\n";
        
        // INSERT DATA
        $result = mysqli_query($koneksi, "SELECT * FROM `$table`");
        if (mysqli_num_rows($result) > 0) {
            $backup_content .= "INSERT INTO `$table` VALUES\n";
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $values = array_map(function($value) use ($koneksi) {
                    return "'" . mysqli_real_escape_string($koneksi, $value) . "'";
                }, array_values($row));
                $rows[] = "(" . implode(', ', $values) . ")";
            }
            $backup_content .= implode(",\n", $rows) . ";\n\n";
        }
    }
    
    if (file_put_contents($backup_file, $backup_content)) {
        $success_msg = "Backup database berhasil dibuat: " . basename($backup_file);
    } else {
        $error_msg = "Gagal membuat backup database.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin Panel</title>
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

        /* Sidebar - Sama seperti sebelumnya */
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

        /* Settings Container */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .settings-section {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .settings-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        /* Form Styles */
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

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
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

        .btn-warning {
            background-color: #ffc107;
            color: var(--dark-color);
        }

        .btn-warning:hover {
            background-color: #e0a800;
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

        /* Info Box */
        .info-box {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: var(--radius);
            margin-top: 20px;
        }

        .info-box h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        /* Back Button */
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--radius);
            text-decoration: none;
            transition: var(--transition);
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: var(--secondary-color);
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
            
            .settings-container {
                grid-template-columns: 1fr;
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
                <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin.php#tambah-berita"><i class="fas fa-plus-circle"></i> Tambah Berita</a></li>
                <li><a href="statistik.php"><i class="fas fa-chart-bar"></i> Statistik</a></li>
                <li><a href="pengaturan.php" class="active"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <div class="logout-btn">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title"><i class="fas fa-cog"></i> Pengaturan Sistem</h1>
                <div class="welcome-msg">
                    <i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                </div>
            </div>

            <a href="admin.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>

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

            <div class="settings-container">
                <!-- Profil Admin -->
                <div class="settings-section">
                    <h3><i class="fas fa-user-cog"></i> Profil Admin</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($admin_data['username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="info-box">
                            <h4>Ubah Password</h4>
                            <p>Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Pengaturan Sistem -->
                <div class="settings-section">
                    <h3><i class="fas fa-sliders-h"></i> Pengaturan Sistem</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="site_title">Judul Portal Berita</label>
                            <input type="text" id="site_title" name="site_title" class="form-control" 
                                   value="<?php echo htmlspecialchars($_SESSION['site_settings']['site_title'] ?? 'Portal Berita'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Deskripsi Portal</label>
                            <textarea id="site_description" name="site_description" class="form-control" 
                                      rows="3"><?php echo htmlspecialchars($_SESSION['site_settings']['site_description'] ?? 'Portal berita terupdate dan terpercaya'); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="items_per_page">Berita per Halaman</label>
                            <select id="items_per_page" name="items_per_page" class="form-control">
                                <option value="5" <?php echo ($_SESSION['site_settings']['items_per_page'] ?? 10) == 5 ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo ($_SESSION['site_settings']['items_per_page'] ?? 10) == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="15" <?php echo ($_SESSION['site_settings']['items_per_page'] ?? 10) == 15 ? 'selected' : ''; ?>>15</option>
                                <option value="20" <?php echo ($_SESSION['site_settings']['items_per_page'] ?? 10) == 20 ? 'selected' : ''; ?>>20</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </form>
                </div>

                <!-- Database Management -->
                <div class="settings-section">
                    <h3><i class="fas fa-database"></i> Manajemen Database</h3>
                    
                    <div class="info-box">
                        <h4>Informasi Database</h4>
                        <p><strong>Nama Database:</strong> portal_berita</p>
                        <p><strong>Tabel:</strong> berita, admin</p>
                        <?php
                        // Hitung ukuran database (estimasi)
                        $size_query = mysqli_query($koneksi, "
                            SELECT 
                                table_schema as db_name,
                                SUM(data_length + index_length) / 1024 / 1024 as db_size_mb
                            FROM information_schema.tables 
                            WHERE table_schema = 'portal_berita'
                            GROUP BY table_schema
                        ");
                        $size_data = mysqli_fetch_assoc($size_query);
                        ?>
                        <p><strong>Ukuran Database:</strong> <?php echo round($size_data['db_size_mb'] ?? 0, 2); ?> MB</p>
                    </div>
                    
                    <form method="POST" style="margin-top: 20px;">
                        <button type="submit" name="backup_database" class="btn btn-warning" onclick="return confirm('Buat backup database sekarang?')">
                            <i class="fas fa-download"></i> Backup Database
                        </button>
                    </form>
                    
                    <div class="info-box" style="margin-top: 20px;">
                        <h4><i class="fas fa-exclamation-triangle"></i> Tindakan Berbahaya</h4>
                        <p>Hanya gunakan jika diperlukan!</p>
                        <form method="POST">
                            <button type="submit" name="reset_stats" class="btn btn-danger" onclick="return confirm('Yakin ingin mereset semua statistik? Tindakan ini tidak dapat dibatalkan!')">
                                <i class="fas fa-trash"></i> Reset Statistik
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Sistem -->
                <div class="settings-section">
                    <h3><i class="fas fa-info-circle"></i> Informasi Sistem</h3>
                    
                    <div class="info-box">
                        <h4>Versi Sistem</h4>
                        <p><strong>Portal Berita CMS:</strong> v1.0.0</p>
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>MySQL Version:</strong> <?php echo mysqli_get_server_info($koneksi); ?></p>
                        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                    </div>
                    
                    <div class="info-box" style="margin-top: 20px;">
                        <h4>Status Sistem</h4>
                        <?php
                        // Cek status folder uploads
                        $uploads_exists = is_dir('uploads');
                        $uploads_writable = $uploads_exists && is_writable('uploads');
                        
                        // Cek status folder backup
                        $backup_exists = is_dir('backup');
                        ?>
                        
                        <p><strong>Folder Uploads:</strong> 
                            <?php if ($uploads_exists && $uploads_writable): ?>
                                <span style="color: green;"><i class="fas fa-check-circle"></i> Tersedia dan dapat ditulis</span>
                            <?php elseif ($uploads_exists && !$uploads_writable): ?>
                                <span style="color: orange;"><i class="fas fa-exclamation-triangle"></i> Tersedia tetapi tidak dapat ditulis</span>
                            <?php else: ?>
                                <span style="color: red;"><i class="fas fa-times-circle"></i> Tidak tersedia</span>
                            <?php endif; ?>
                        </p>
                        
                        <p><strong>Folder Backup:</strong> 
                            <?php if ($backup_exists): ?>
                                <span style="color: green;"><i class="fas fa-check-circle"></i> Tersedia</span>
                            <?php else: ?>
                                <span style="color: orange;"><i class="fas fa-exclamation-triangle"></i> Tidak tersedia</span>
                            <?php endif; ?>
                        </p>
                        
                        <p><strong>Koneksi Database:</strong> 
                            <?php if ($koneksi): ?>
                                <span style="color: green;"><i class="fas fa-check-circle"></i> Terhubung</span>
                            <?php else: ?>
                                <span style="color: red;"><i class="fas fa-times-circle"></i> Terputus</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
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

        // Confirm sebelum reset statistik
        document.querySelector('[name="reset_stats"]')?.addEventListener('click', function(e) {
            if (!confirm('Yakin ingin mereset semua statistik? Tindakan ini tidak dapat dibatalkan!')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>