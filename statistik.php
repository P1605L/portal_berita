<?php
include 'config.php';
session_start();

// Redirect jika belum login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil statistik
$total_berita = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita");
$total_berita = mysqli_fetch_assoc($total_berita)['total'];

$total_berita_hari_ini = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita WHERE DATE(tanggal_publish) = CURDATE()");
$total_berita_hari_ini = mysqli_fetch_assoc($total_berita_hari_ini)['total'];

$total_berita_bulan_ini = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM berita WHERE MONTH(tanggal_publish) = MONTH(CURDATE()) AND YEAR(tanggal_publish) = YEAR(CURDATE())");
$total_berita_bulan_ini = mysqli_fetch_assoc($total_berita_bulan_ini)['total'];

// Statistik per kategori
$kategori_stats = mysqli_query($koneksi, "SELECT kategori, COUNT(*) as total FROM berita GROUP BY kategori ORDER BY total DESC");

// Statistik per bulan (6 bulan terakhir)
$bulan_stats = mysqli_query($koneksi, "
    SELECT 
        DATE_FORMAT(tanggal_publish, '%Y-%m') as bulan,
        COUNT(*) as total
    FROM berita 
    WHERE tanggal_publish >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal_publish, '%Y-%m')
    ORDER BY bulan
");

// Statistik penulis
$penulis_stats = mysqli_query($koneksi, "SELECT penulis, COUNT(*) as total FROM berita GROUP BY penulis ORDER BY total DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Sidebar - Sama seperti admin.php */
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

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            font-size: 1rem;
            color: var(--gray-color);
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-card .stat-change {
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .positive {
            color: #28a745;
        }

        .negative {
            color: var(--accent-color);
        }

        /* Chart Container */
        .chart-container {
            background-color: white;
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .chart-title {
            margin-bottom: 20px;
            color: var(--primary-color);
            font-size: 1.3rem;
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
                <li><a href="statistik.php" class="active"><i class="fas fa-chart-bar"></i> Statistik</a></li>
                <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
            <div class="logout-btn">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title"><i class="fas fa-chart-bar"></i> Statistik Portal Berita</h1>
                <div class="welcome-msg">
                    <i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                </div>
            </div>

            <a href="admin.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-newspaper"></i> Total Berita</h3>
                    <div class="stat-value"><?php echo $total_berita; ?></div>
                    <div class="stat-change">
                        <span class="positive">+<?php echo $total_berita_hari_ini; ?> hari ini</span>
                    </div>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-calendar-day"></i> Berita Hari Ini</h3>
                    <div class="stat-value"><?php echo $total_berita_hari_ini; ?></div>
                    <div class="stat-change">
                        <?php 
                        $percentage = $total_berita > 0 ? round(($total_berita_hari_ini / $total_berita) * 100, 1) : 0;
                        echo $percentage . "% dari total";
                        ?>
                    </div>
                </div>

                <div class="stat-card">
                    <h3><i class="fas fa-calendar-alt"></i> Berita Bulan Ini</h3>
                    <div class="stat-value"><?php echo $total_berita_bulan_ini; ?></div>
                    <div class="stat-change">
                        Rata-rata: <?php echo round($total_berita_bulan_ini / date('j'), 1); ?>/hari
                    </div>
                </div>
            </div>

            <!-- Chart: Berita per Kategori -->
            <div class="chart-container">
                <h3 class="chart-title">Berita per Kategori</h3>
                <canvas id="kategoriChart"></canvas>
            </div>

            <!-- Chart: Trend Berita 6 Bulan Terakhir -->
            <div class="chart-container">
                <h3 class="chart-title">Trend Berita (6 Bulan Terakhir)</h3>
                <canvas id="trendChart"></canvas>
            </div>

            <!-- Tabel: Statistik per Kategori -->
            <div class="chart-container">
                <h3 class="chart-title">Detail Statistik per Kategori</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Jumlah Berita</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $kategori_stats_data = [];
                        while ($row = mysqli_fetch_assoc($kategori_stats)): 
                            $percentage = $total_berita > 0 ? round(($row['total'] / $total_berita) * 100, 1) : 0;
                            $kategori_stats_data[] = $row;
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo ucfirst($row['kategori']); ?></td>
                            <td><?php echo $row['total']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 100%; background-color: #e3f2fd; border-radius: 20px; margin-right: 10px;">
                                        <div style="width: <?php echo $percentage; ?>%; background-color: var(--primary-color); height: 8px; border-radius: 20px;"></div>
                                    </div>
                                    <span><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel: Top 10 Penulis -->
            <div class="chart-container">
                <h3 class="chart-title">Top 10 Penulis</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Penulis</th>
                            <th>Jumlah Berita</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($penulis_stats)): 
                            $percentage = $total_berita > 0 ? round(($row['total'] / $total_berita) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['penulis']); ?></td>
                            <td><?php echo $row['total']; ?></td>
                            <td><?php echo $percentage; ?>%</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Data untuk chart kategori
        const kategoriLabels = [
            <?php 
            foreach ($kategori_stats_data as $kategori) {
                echo "'" . ucfirst($kategori['kategori']) . "',";
            }
            ?>
        ];
        
        const kategoriData = [
            <?php 
            foreach ($kategori_stats_data as $kategori) {
                echo $kategori['total'] . ",";
            }
            ?>
        ];

        // Warna untuk chart kategori
        const kategoriColors = [
            '#1a365d', '#2a4a7f', '#3d5a80', '#4d7ea8', '#5c9dcd',
            '#e63946', '#f4a261', '#2a9d8f', '#e9c46a', '#264653'
        ];

        // Chart: Berita per Kategori
        const kategoriCtx = document.getElementById('kategoriChart').getContext('2d');
        const kategoriChart = new Chart(kategoriCtx, {
            type: 'pie',
            data: {
                labels: kategoriLabels,
                datasets: [{
                    data: kategoriData,
                    backgroundColor: kategoriColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' berita';
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Data untuk trend chart
        <?php
        $trendLabels = [];
        $trendData = [];
        while ($row = mysqli_fetch_assoc($bulan_stats)) {
            $trendLabels[] = date('M Y', strtotime($row['bulan'] . '-01'));
            $trendData[] = $row['total'];
        }
        ?>

        const trendLabels = [<?php echo "'" . implode("','", $trendLabels) . "'"; ?>];
        const trendData = [<?php echo implode(",", $trendData); ?>];

        // Chart: Trend Berita
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Jumlah Berita',
                    data: trendData,
                    backgroundColor: 'rgba(26, 54, 93, 0.1)',
                    borderColor: 'rgba(26, 54, 93, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Berita'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>