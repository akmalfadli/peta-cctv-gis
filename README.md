# 📹 Modul Peta CCTV & GIS Desa (PetaCCTV)

Modul **PetaCCTV** adalah platform Sistem Informasi Geografis (GIS) terpadu untuk lingkungan **OpenSID** yang dirancang khusus untuk menampilkan lokasi kamera pemantauan (CCTV) langsung (live streaming) serta penandaan koordinat geografis Point of Interest (POI) desa—seperti Sekolah, Masjid, Kantor Desa, Tempat Wisata, Pasar, dan Fasilitas Kesehatan.

Modul ini telah ditingkatkan dari pemantau video sederhana menjadi utilitas pemetaan wilayah desa yang komprehensif, cepat, dan mudah dikelola oleh perangkat desa.

---

## 🚀 Fitur Utama

### 1. 🗺️ Visualisasi GIS Interaktif (Frontend)
*   **Peta Layar Penuh (LeafletJS)**: Antarmuka peta yang cepat dan responsif dengan fitur pengklasteran marker (*MarkerCluster*) untuk performa optimal pada perangkat seluler maupun desktop.
*   **Penanda Berbasis Kategori (Category-Aware Markers)**: Penanda koordinat yang secara dinamis menetapkan ikon FontAwesome khusus serta warna latar belakang berdasarkan kata kunci kategori (misalnya: Sekolah warna biru, Masjid warna teal, Wisata warna pink, dsb).
*   **Dukungan Marker Koordinat Saja**: Memungkinkan admin menambahkan penanda fasilitas penting desa tanpa memerlukan URL siaran langsung. Penanda ini akan diberi tanda khusus `● TEMPAT` dan dilabeli sebagai *Hanya Marker Lokasi*.
*   **Pemutar Video Mode Bioskop (Theater Mode Modal)**: Popup pemutar video *live stream* premium yang bersih, tanpa overlay teks waktu/tanggal bawaan yang mengganggu pemandangan siaran langsung.
*   **Legenda Peta Pintar**: Legenda interaktif di sudut kiri bawah peta untuk memudahkan masyarakat mengenali jenis penanda geografis desa.
*   **Multi-Tileset Switcher**: Memungkinkan pengguna beralih jenis peta antara peta jalan (OSM), peta satelit, dan peta minimalis abu-abu (*gray*).

### 2. ⚙️ Manajemen Administrasi Premium (Backend)
*   **Dashboard Statistik Telemetri**: Menampilkan 4 kartu statistik ringkas: Total Kamera, Kamera Online, Kamera Offline, dan Kamera Nonaktif dengan efek animasi saat kursor diarahkan.
*   **Formulir Fleksibel (Optional Streams)**: Mengizinkan pembuatan entri lokasi murni koordinat. Pilihan *Tanpa Stream* secara dinamis mematikan kewajiban pengisian URL.
*   **Sistem Telemetri Cek Kesehatan Otomatis (Health Check)**: Fitur pengecekan koneksi streaming yang melakukan ping berkala ke server CCTV. Kamera tanpa stream secara cerdas dilewati (*bypassed*) agar tetap ditandai "Online" tanpa memperlambat performa server.
*   **Pencarian & Penyaringan Server-Side**: Menggunakan integrasi *DataTables* berkinerja tinggi untuk memilah data berdasarkan Kategori, Status Kesehatan, Keaktifan, dan Visibilitas Publik.
*   **Sakelar Mini (Ultra-Compact Toggle Switches)**: Antarmuka sakelar keaktifan dan visibilitas publik yang super ringkas (lebar `60px` dengan tinggi `18px`) dan hemat ruang pada baris tabel administrasi.

---

## 🛠️ Arsitektur Teknologi

*   **Framework Core**: OpenSID / CodeIgniter 3
*   **Peta Digital**: LeafletJS v1.9.4 & Leaflet.markercluster
*   **Streaming Engine**: Hls.js (untuk protokol HLS/M3U8), Dukungan Embed YouTube, dan IFrame Umum.
*   **Desain Antarmuka**: Tailwind CSS (Frontend) & AdminLTE Bootstrap v3 (Backend).
*   **Ikonografi**: FontAwesome v6.4.0 (Akses ke ribuan ikon modern).
*   **Data Transport**: AJAX, Datatables Server-Side, & RESTful JSON endpoints.

---

## 📁 Struktur Direktori

```bash
Modules/PetaCCTV/
├── 1_cctv_setup_manual.sql  # SQL dasar untuk inisialisasi tabel
├── Config/                  # Konfigurasi Modul
├── Database/
│   ├── Migrations/          # Migrasi Skema Database
│   └── Seeders/             # Data Awal / Contoh Kategori & Kamera
├── Http/
│   └── Controllers/
│       ├── BackEnd/
│       │   └── CctvAdminController.php # Kontroler Administrasi (Backend)
│       └── FrontEnd/
│           └── CctvController.php      # Kontroler Peta Publik (Frontend)
├── Models/
│   ├── CctvCamera.php       # Model Data Kamera & Tempat
│   └── CctvCategory.php     # Model Data Kategori Tempat
├── Views/
│   ├── backend/
│   │   └── camera/
│   │       ├── form.blade.php  # Form Tambah/Ubah Kamera & Tempat
│   │       └── index.blade.php # Daftar Kamera & Dashboard Admin
│   └── frontend/
│       └── map.blade.php    # Peta Publik GIS & CCTV Utama
└── README.md                # Dokumentasi Modul (Bahasa Indonesia)
```

---

## 🗄️ Skema Database

Modul ini menggunakan dua tabel utama di dalam database:

### 1. `cctv_categories`
Menyimpan nama-nama kategori tempat dan status keaktifan.
*   `id` (INT, Primary Key)
*   `name` (VARCHAR, Nama Kategori, e.g. "Masjid", "Sekolah")
*   `is_active` (TINYINT, Status Aktif)

### 2. `cctv_cameras`
Menyimpan detail CCTV dan Marker Geografis Tempat.
*   `id` (INT, Primary Key)
*   `config_id` (INT, Relasi Identitas Desa)
*   `category_id` (INT, Relasi ke `cctv_categories`)
*   `name` (VARCHAR, Nama Lokasi / CCTV)
*   `description` (TEXT, Keterangan Lokasi)
*   `latitude` (DECIMAL 10,8, Koordinat Lintang)
*   `longitude` (DECIMAL 11,8, Koordinat Bujur)
*   `stream_url` (VARCHAR, nullable, URL Siaran Langsung)
*   `stream_type` (VARCHAR, nullable, Tipe Stream: `hls`, `youtube`, `iframe`, atau `""` untuk tanpa stream)
*   `thumbnail` (VARCHAR, nullable, Gambar Cover / Thumbnail Tempat)
*   `status` (VARCHAR, Status Online/Offline: `online`, `offline`)
*   `is_public` (TINYINT, Visibilitas Publik)
*   `is_active` (TINYINT, Status Aktif)
*   `last_online_at` (DATETIME, Waktu Terakhir Terdeteksi Online)

---

## 🎨 Logika Pemetaan Ikon Kategori (GIS)

Penanda peta publik akan membaca nama kategori untuk menetapkan ikon dan warna secara otomatis menggunakan utilitas JavaScript berikut di `map.blade.php`:

| Kategori Mengandung Kata Kunci | Ikon Penanda | Warna Penanda | Contoh Penggunaan |
| :--- | :---: | :---: | :--- |
| `cctv`, `kamera`, `pemantauan` | <i class="fa-solid fa-video"></i> | Biru Dongker (`#1e3a8a`) | CCTV Balai Desa, CCTV Pertigaan |
| `sekolah`, `sd`, `smp`, `sma`, `smk` | <i class="fa-solid fa-graduation-cap"></i> | Biru Terang (`#3b82f6`) | SMK N 1 Perwira, SD Negeri 1 |
| `masjid`, `mushola`, `surau`, `islamic` | <i class="fa-solid fa-mosque"></i> | Teal / Toska (`#0d9488`) | Masjid Raya Annur, Mushola Al-Ikhlas |
| `kantor`, `office`, `dinas`, `balai` | <i class="fa-solid fa-building"></i> | Abu-abu Slate (`#475569`) | Balai Desa, Kantor Kecamatan |
| `wisata`, `tourist`, `pantai`, `taman` | <i class="fa-solid fa-mountain-sun"></i> | Merah Muda (`#db2777`) | Curug Perwira, Taman Rekreasi |
| `pasar`, `market`, `toko`, `warung` | <i class="fa-solid fa-store"></i> | Oranye (`#ea580c`) | Pasar Desa, Toko Sembako BUMDes |
| `puskesmas`, `klinik`, `posyandu`, `medis` | <i class="fa-solid fa-house-chimney-medical"></i>| Merah Rose (`#e11d48`) | Puskesmas Pembantu, Posyandu Dahlia |

---

## ⚙️ Petunjuk Operasional & Pemeliharaan

### 📌 Menjalankan Pengecekan Status Stream Otomatis (Cron Job)
Agar status `Online` / `Offline` CCTV terupdate secara otomatis tanpa harus menekan tombol manual di admin, Anda dapat mendaftarkan cron job pada server hosting Anda. 

Jalankan perintah berikut setiap **5 atau 10 menit**:
```bash
*/5 * * * * curl -s http://domain-desa.id/index.php/cctv_admin/health_check > /dev/null 2>&1
```
*Sistem telemetri ini secara otomatis mengabaikan marker berlabel **Tanpa Stream** sehingga tidak membebani penggunaan CPU server.*

### 📍 Cara Mendapatkan Koordinat Latitude & Longitude
1. Buka [Google Maps](https://maps.google.com).
2. Cari lokasi yang ingin ditandai.
3. Klik kanan pada titik lokasi tersebut di peta.
4. Klik angka koordinat yang muncul (misalnya: `-7.398311, 109.543266`) untuk menyalinnya secara otomatis.
5. Tempelkan angka koordinat depan (misal: `-7.398311`) ke kolom **Latitude** dan angka belakang (misal: `109.543266`) ke kolom **Longitude** di form admin.

---

## 📌 Rencana Pengembangan Selanjutnya (Roadmap)
Untuk masa mendatang, modul ini sangat direkomendasikan untuk ditambahkan:
1. **Interactive Location Picker**: Peta mini di formulir tambah/edit lokasi agar admin cukup mengklik peta untuk mendapatkan koordinat secara instan.
2. **"Rute Navigasi" Button**: Menambahkan tombol penunjuk arah di popup marker publik yang terintegrasi langsung dengan rute Google Maps.
3. **Pemberitahuan Darurat (Alert Tag)**: Menambahkan penanda darurat berkedip jika terjadi bencana alam, jalan ditutup, atau kegiatan warga.
