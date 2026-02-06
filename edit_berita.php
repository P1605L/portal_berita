<?php
include 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success_msg = '';
$error_msg = '';

// Ambil data berita yang akan diedit
if ($id > 0) {
    $query = "SELECT * FROM berita WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    $berita = mysqli_fetch_assoc($result);
    
    if (!$berita) {
        $error_msg = "Berita tidak ditemukan!";
    }
} else {
    header('Location: admin.php');
    exit;
}

// Update berita
if (isset($_POST['update_berita'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $isi = mysqli_real_escape_string($koneksi, $_POST['isi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $penulis = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    
    // Cek jika ada file gambar baru
    if (!empty($_FILES['gambar']['name'])) {
        $gambar_name = $_FILES['gambar']['name'];
        $gambar_tmp = $_FILES['gambar']['tmp_name'];
        $gambar_ext = strtolower(pathinfo($gambar_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi file
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($gambar_ext, $allowed_ext)) {
            $error_msg = "Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
        } else {
            // Hapus gambar lama
            if (!empty($berita['gambar']) && file_exists($berita['gambar'])) {
                unlink($berita['gambar']);
            }
            
            // Upload gambar baru
            $new_gambar_name = uniqid('berita_', true) . '.' . $gambar_ext;
            $gambar_path = "uploads/" . $new_gambar_name;
            
            if (move_uploaded_file($gambar_tmp, $gambar_path)) {
                $query = "UPDATE berita SET 
                          judul = '$judul', 
                          isi = '$isi', 
                          kategori = '$kategori', 
                          gambar = '$gambar_path', 
                          penulis = '$penulis' 
                          WHERE id = $id";
            } else {
                $error_msg = "Gagal mengupload gambar.";
            }
        }
    } else {
        // Update tanpa mengganti gambar
        $query = "UPDATE berita SET 
                  judul = '$judul', 
                  isi = '$isi', 
                  kategori = '$kategori', 
                  penulis = '$penulis' 
                  WHERE id = $id";
    }
    
    if (!isset($error_msg) || empty($error_msg)) {
        if (mysqli_query($koneksi, $query)) {
            $success_msg = "Berita berhasil diperbarui!";
            // Refresh data berita
            $query = "SELECT * FROM berita WHERE id = $id";
            $result = mysqli_query($koneksi, $query);
            $berita = mysqli_fetch_assoc($result);
        } else {
            $error_msg = "Error: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - Admin Panel</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .title {
            font-size: 1.8rem;
            color: var(--primary-color);
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
            margin-right: 10px;
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

        /* Preview Gambar */
        .image-preview {
            margin-top: 10px;
        }

        .current-image {
            max-width: 200px;
            height: auto;
            border-radius: var(--radius);
            border: 1px solid #ddd;
            padding: 5px;
            background-color: white;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="title"><i class="fas fa-edit"></i> Edit Berita</h1>
            <a href="admin.php" class="btn">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
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

        <!-- Form Edit Berita -->
        <div class="admin-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Berita</label>
                    <input type="text" id="judul" name="judul" class="form-control" 
                           value="<?php echo htmlspecialchars($berita['judul'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="isi">Isi Berita</label>
                    <textarea id="isi" name="isi" class="form-control" required><?php echo htmlspecialchars($berita['isi'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        <option value="nasional" <?php echo ($berita['kategori'] ?? '') == 'nasional' ? 'selected' : ''; ?>>Nasional</option>
                        <option value="internasional" <?php echo ($berita['kategori'] ?? '') == 'internasional' ? 'selected' : ''; ?>>Internasional</option>
                        <option value="ekonomi" <?php echo ($berita['kategori'] ?? '') == 'ekonomi' ? 'selected' : ''; ?>>Ekonomi</option>
                        <option value="teknologi" <?php echo ($berita['kategori'] ?? '') == 'teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="olahraga" <?php echo ($berita['kategori'] ?? '') == 'olahraga' ? 'selected' : ''; ?>>Olahraga</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar</label>
                    <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>
                    
                    <?php if (!empty($berita['gambar'])): ?>
                    <div class="image-preview">
                        <p><strong>Gambar saat ini:</strong></p>
                        <img src="<?php echo $berita['gambar']; ?>" alt="Preview" class="current-image">
                        <p><small><?php echo basename($berita['gambar']); ?></small></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="penulis">Penulis</label>
                    <input type="text" id="penulis" name="penulis" class="form-control" 
                           value="<?php echo htmlspecialchars($berita['penulis'] ?? ''); ?>" required>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" name="update_berita" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Berita
                    </button>
                    
                    <a href="admin.php" class="btn">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>

        <!-- Informasi Berita -->
        <div class="admin-form">
            <h3><i class="fas fa-info-circle"></i> Informasi Berita</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div>
                    <p><strong>ID Berita:</strong> <?php echo $berita['id'] ?? 'N/A'; ?></p>
                </div>
                <div>
                    <p><strong>Tanggal Publish:</strong> <?php echo date('d/m/Y H:i', strtotime($berita['tanggal_publish'] ?? '')); ?></p>
                </div>
                <div>
                    <p><strong>Terakhir Update:</strong> <?php echo date('d/m/Y H:i'); ?></p>
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

        // Preview gambar sebelum upload
        document.getElementById('gambar')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainer = document.querySelector('.image-preview') || 
                        (() => {
                            const container = document.createElement('div');
                            container.className = 'image-preview';
                            e.target.parentNode.appendChild(container);
                            return container;
                        })();
                    
                    previewContainer.innerHTML = `
                        <p><strong>Preview gambar baru:</strong></p>
                        <img src="${e.target.result}" alt="Preview" class="current-image">
                        <p><small>${file.name}</small></p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>