DROP DATABASE IF EXISTS portal_berita;
CREATE DATABASE portal_berita;
USE portal_berita;

-- Tabel berita
CREATE TABLE berita (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    kategori ENUM('nasional', 'internasional', 'ekonomi', 'teknologi', 'olahraga') NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    tanggal_publish DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel admin
CREATE TABLE admin (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert data admin
INSERT INTO admin (username, password) VALUES 
('admin', MD5('admin123'));

-- Insert data berita nasional
INSERT INTO berita (judul, isi, kategori, gambar, penulis) VALUES
('Presiden Jokowi Resmikan Jalan Tol Trans Jawa', 'Presiden Joko Widodo secara resmi meresmikan Jalan Tol Trans Jawa seksi terakhir yang menghubungkan seluruh Pulau Jawa. Proyek strategis ini diharapkan dapat meningkatkan konektivitas dan perekonomian di sepanjang koridor Jawa.', 'nasional', 'nasional1.jpg', 'Budi Santoso'),
('Pemilu 2024: KPU Umumkan Jadwal Pendaftaran', 'Komisi Pemilihan Umum (KPU) telah mengumumkan jadwal pendaftaran untuk Pemilu 2024. Masyarakat dapat mendaftar mulai tanggal 1 Oktober 2023 hingga 31 Januari 2024 melalui aplikasi atau kantor KPU setempat.', 'nasional', 'nasional2.jpg', 'Sari Dewi'),
('Indonesia Terima Bantuan Vaksin dari Jepang', 'Pemerintah Jepang memberikan bantuan vaksin COVID-19 sebanyak 2 juta dosis kepada Indonesia. Bantuan ini merupakan bagian dari kerja sama kesehatan antara kedua negara dalam menangani pandemi global.', 'nasional', 'nasional3.jpg', 'Ahmad Fauzi'),
('Gubernur DKI Resmikan Rumah DP 0 Rupiah', 'Gubernur DKI Jakarta Anies Baswedan meresmikan program rumah dengan DP 0 rupiah untuk masyarakat berpenghasilan rendah. Program ini diharapkan dapat membantu warga Jakarta memiliki tempat tinggal yang layak.', 'nasional', 'nasional4.jpg', 'Rina Marlina'),
('Banjir Landa Jakarta, Pemprov Siagakan 500 Personel', 'Banjir kembali melanda sejumlah wilayah di Jakarta setelah hujan deras selama 5 jam. Pemerintah Provinsi DKI Jakarta telah menyiagakan 500 personel untuk melakukan evakuasi dan penanganan darurat.', 'nasional', 'nasional5.jpg', 'Joko Priyono');

-- Insert data berita internasional
INSERT INTO berita (judul, isi, kategori, gambar, penulis) VALUES
('Konferensi Perdamaian Dunia Digelar di Jenewa', 'Para pemimpin dunia berkumpul di Jenewa, Swiss untuk menghadiri Konferensi Perdamaian Dunia. Konferensi ini membahas berbagai isu global termasuk konflik regional, perubahan iklim, dan kerjasama internasional.', 'internasional', 'internasional1.jpg', 'Michael Chen'),
('Amerika Serikat dan Tiongkok Sepakat Lanjutkan Perundingan Dagang', 'Amerika Serikat dan Tiongkok menyepakati untuk melanjutkan perundingan perdagangan yang sempat terhenti. Kedua negara berkomitmen untuk mencari solusi yang menguntungkan kedua belah pihak dalam sengketa dagang mereka.', 'internasional', 'internasional2.jpg', 'Sarah Johnson'),
('PBB Laporkan Kemajuan dalam Penanganan Perubahan Iklim', 'Perserikatan Bangsa-Bangsa (PBB) melaporkan adanya kemajuan signifikan dalam penanganan perubahan iklim global. Sebanyak 150 negara telah meningkatkan komitmen mereka dalam mengurangi emisi karbon.', 'internasional', 'internasional3.jpg', 'David Wilson'),
('Uni Eropa Umumkan Paket Bantuan untuk Ukraina', 'Uni Eropa mengumumkan paket bantuan ekonomi senilai 500 juta euro untuk Ukraina. Bantuan ini ditujukan untuk mendukung pemulihan ekonomi dan pembangunan infrastruktur di negara tersebut.', 'internasional', 'internasional4.jpg', 'Emma Thompson'),
('Jepang Luncurkan Satelit Pemantau Bencana Terbaru', 'Jepang berhasil meluncurkan satelit pemantau bencana terbaru yang dilengkapi dengan teknologi canggih. Satelit ini akan digunakan untuk memantau gempa bumi, tsunami, dan bencana alam lainnya di kawasan Asia Pasifik.', 'internasional', 'internasional5.jpg', 'Kenji Tanaka');

-- Insert data berita ekonomi
INSERT INTO berita (judul, isi, kategori, gambar, penulis) VALUES
('IHSG Naik 1,5% di Penutupan Perdagangan Hari Ini', 'Indeks Harga Saham Gabungan (IHSG) menguat 1,5% pada penutupan perdagangan hari ini. Penguatan terjadi didorong oleh membesinya sektor perbankan dan properti yang menunjukkan kinerja positif.', 'ekonomi', 'ekonomi1.jpg', 'Dewi Anggraeni'),
('Bank Indonesia Pertahankan Suku Bunga Acuan di 5,75%', 'Bank Indonesia memutuskan untuk mempertahankan suku bunga acuan (BI 7-Day Reverse Repo Rate) di level 5,75% pada rapat dewan gubernur bulan ini. Keputusan ini diambil untuk menjaga stabilitas nilai tukar rupiah.', 'ekonomi', 'ekonomi2.jpg', 'Robert Wijaya'),
('Rupiah Menguat terhadap Dolar AS', 'Nilai tukar rupiah terhadap dolar AS menguat pada perdagangan hari ini. Rupiah ditutup di level Rp15.200 per dolar AS, menguat Rp150 dari penutupan perdagangan kemarin.', 'ekonomi', 'ekonomi3.jpg', 'Linda Sari'),
('Ekspor Indonesia Tumbuh 15% pada Kuartal III', 'Badan Pusat Statistik melaporkan pertumbuhan ekspor Indonesia sebesar 15% pada kuartal III tahun 2023. Pertumbuhan terbesar berasal dari sektor migas, perkebunan, dan produk olahan.', 'ekonomi', 'ekonomi4.jpg', 'Agus Setiawan'),
('Investor Asing Ramaikan Pasar Obligasi Indonesia', 'Investor asing mulai kembali meramaikan pasar obligasi Indonesia dengan membeli surat utang negara senilai Rp10 triliun dalam sepekan terakhir. Minat investor meningkat seiring dengan stabilnya kondisi ekonomi.', 'ekonomi', 'ekonomi5.jpg', 'Maya Puspita');

-- Insert data berita teknologi
INSERT INTO berita (judul, isi, kategori, gambar, penulis) VALUES
('Startup Indonesia Raih Pendanaan $100 Juta', 'Startup teknologi asal Indonesia, GoDigital, berhasil mengantong pendanaan Series C senilai $100 juta dari investor global. Pendanaan ini akan digunakan untuk ekspansi ke pasar Asia Tenggara.', 'teknologi', 'teknologi1.jpg', 'Rizky Pratama'),
('Apple Rilis iPhone 15 dengan Fitur AI Terbaru', 'Apple secara resmi meluncurkan iPhone 15 yang dilengkapi dengan fitur kecerdasan buatan (AI) terbaru. Ponsel ini memiliki kemampuan pemrosesan gambar dan suara yang lebih canggih dibanding generasi sebelumnya.', 'teknologi', 'teknologi2.jpg', 'Alexandra Lee'),
('Google Umumkan Pembaruan Algorithm Search', 'Google mengumumkan pembaruan besar-besaran pada algoritma pencariannya. Pembaruan ini fokus pada pengalaman pengguna dan konten yang lebih relevan, serta memprioritaskan situs dengan kecepatan loading yang baik.', 'teknologi', 'teknologi3.jpg', 'Brian O''Connor'),
('Kecerdasan Buatan Ubah Cara Kerja Industri', 'Perkembangan kecerdasan buatan (AI) telah mengubah cara kerja di berbagai industri. Dari manufaktur hingga layanan kesehatan, AI membantu meningkatkan efisiensi dan akurasi dalam proses produksi dan pelayanan.', 'teknologi', 'teknologi4.jpg', 'Nina Susanto'),
('5G Resmi Diluncurkan di 10 Kota Besar Indonesia', 'Operator telekomunikasi resmi meluncurkan layanan 5G di 10 kota besar Indonesia. Layanan ini menawarkan kecepatan internet hingga 10 kali lebih cepat dibanding jaringan 4G yang ada saat ini.', 'teknologi', 'teknologi5.jpg', 'Fajar Ramadan');

-- Insert data berita olahraga
INSERT INTO berita (judul, isi, kategori, gambar, penulis) VALUES
('Timnas Indonesia Lolos ke Piala Asia 2023', 'Tim nasional Indonesia berhasil lolos ke Piala Asia 2023 setelah mengalahkan Vietnam dengan skor 2-1. Kemenangan ini membuat Indonesia berada di peringkat kedua grup kualifikasi.', 'olahraga', 'olahraga1.jpg', 'Andi Wijaya'),
('Persib vs Persija Berakhir Imbang 1-1', 'Laga sengit antara Persib Bandung dan Persija Jakarta berakhir imbang 1-1 di Stadion Gelora Bandung Lautan Api. Kedua tim menunjukkan permainan yang menarik dengan serangan balik yang cepat.', 'olahraga', 'olahraga2.jpg', 'Bambang Hartono'),
('Atlet Bulutangkis Indonesia Raih Emas di SEA Games', 'Atlet bulutangkis Indonesia berhasil meraih medali emas pada nomor ganda putra di SEA Games. Pasangan Kevin/Marcus mengalahkan wakil Malaysia dengan skor 21-19, 21-17 dalam final yang menegangkan.', 'olahraga', 'olahraga3.jpg', 'Siti Rahayu'),
('Formula 1: Max Verstappen Juara Musim Ini', 'Pembalap Red Bull Racing, Max Verstappen, resmi dinobatkan sebagai juara dunia Formula 1 musim ini. Verstappen meraih gelar setelah finis di posisi kedua pada Grand Prix Jepang.', 'olahraga', 'olahraga4.jpg', 'Rudi Hermawan'),
('Atlet Renang Indonesia Pecahkan Rekor Nasional', 'Atlet renang Indonesia, I Gede Siman, berhasil memecahkan rekor nasional pada nomor 100 meter gaya bebas dengan catatan waktu 48,56 detik. Prestasi ini dicapai pada kejuaraan renang Asia Tenggara di Singapura.', 'olahraga', 'olahraga5.jpg', 'Putri Lestari');