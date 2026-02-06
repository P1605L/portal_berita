<?php
// CONFIG.PHP - Versi Lengkap dengan Fungsi

// ============================================
// KONEKSI DATABASE
// ============================================
$host = "localhost";
$user = "root";
$password = "";
$database = "portal_berita";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    // Jangan tampilkan error detail di produksi
    die("Database connection failed. Please check your database configuration.");
}

// ============================================
// FUNGSI GET BERITA
// ============================================
if (!function_exists('getBerita')) {
    function getBerita($koneksi, $limit = null, $kategori = null, $search = null) {
        // Pastikan koneksi valid
        if (!$koneksi) {
            return [];
        }
        
        // Mulai query
        $query = "SELECT * FROM berita WHERE 1=1";
        
        // Filter kategori
        if ($kategori) {
            $kategori_clean = mysqli_real_escape_string($koneksi, $kategori);
            $query .= " AND kategori = '$kategori_clean'";
        }
        
        // Filter pencarian
        if ($search) {
            $search_clean = mysqli_real_escape_string($koneksi, $search);
            $query .= " AND (judul LIKE '%$search_clean%' OR isi LIKE '%$search_clean%')";
        }
        
        // Urutkan
        $query .= " ORDER BY tanggal_publish DESC";
        
        // Limit hasil
        if ($limit && is_numeric($limit)) {
            $query .= " LIMIT " . intval($limit);
        }
        
        // Eksekusi query
        $result = mysqli_query($koneksi, $query);
        
        // Jika query gagal, return array kosong
        if (!$result) {
            return [];
        }
        
        // Ambil hasil
        $berita = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $berita[] = $row;
        }
        
        return $berita;
    }
}

// ============================================
// FUNGSI TAMBAHAN (OPTIONAL)
// ============================================
if (!function_exists('getTotalBerita')) {
    function getTotalBerita($koneksi, $kategori = null) {
        if (!$koneksi) return 0;
        
        $query = "SELECT COUNT(*) as total FROM berita";
        if ($kategori) {
            $kategori_clean = mysqli_real_escape_string($koneksi, $kategori);
            $query .= " WHERE kategori = '$kategori_clean'";
        }
        
        $result = mysqli_query($koneksi, $query);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['total'];
        }
        
        return 0;
    }
}
?>