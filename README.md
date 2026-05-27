# 📹 Modul Peta CCTV & GIS Desa (PetaCCTV)

Modul **PetaCCTV** adalah platform Sistem Informasi Geografis (GIS) terpadu untuk lingkungan **OpenSID** yang dirancang khusus untuk menampilkan lokasi kamera pemantauan (CCTV) langsung (live streaming) serta penandaan koordinat geografis Point of Interest (POI) desa—seperti Sekolah, Masjid, Kantor Desa, Tempat Wisata, Pasar, dan Fasilitas Kesehatan.

Modul ini telah ditingkatkan dari pemantau video sederhana menjadi utilitas pemetaan wilayah desa yang komprehensif, cepat, mandiri (plug-and-play), dan mudah dikelola oleh perangkat desa.

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
*   **Interactive Location Picker (GIS Form Map)**: Dilengkapi peta interaktif mini LeafletJS pada formulir Tambah & Edit Lokasi. Admin cukup mengklik peta atau menyeret penanda (*draggable marker*) merah untuk mengisi koordinat `Latitude` & `Longitude` secara otomatis.
*   **Dashboard Statistik Telemetri**: Menampilkan 4 kartu statistik ringkas: Total Kamera, Kamera Online, Kamera Offline, dan Kamera Nonaktif dengan efek animasi saat kursor diarahkan.
*   **Formulir Fleksibel (Optional Streams)**: Mengizinkan pembuatan entri lokasi murni koordinat. Pilihan *Tanpa Stream* secara dinamis mematikan kewajiban pengisian URL.
*   **Sistem Telemetri Cek Kesehatan Otomatis (Health Check)**: Fitur pengecekan koneksi streaming yang melakukan ping berkala ke server CCTV. Kamera tanpa stream secara cerdas dilewati (*bypassed*) agar tetap ditandai "Online" tanpa memperlambat performa server.
*   **Pencarian & Penyaringan Server-Side**: Menggunakan integrasi *DataTables* berkinerja tinggi untuk memilah data berdasarkan Kategori, Status Kesehatan, Keaktifan, dan Visibilitas Publik.
*   **Sakelar Mini (Ultra-Compact Toggle Switches)**: Antarmuka sakelar keaktifan dan visibilitas publik yang super ringkas (lebar `60px` dengan tinggi `18px`) dan hemat ruang pada baris tabel administrasi.

### 3. 🌤️ Widget Informasi Cuaca Desa (Weather Widget)
*   **Integrasi OpenWeatherMap API**: Menampilkan ramalan cuaca real-time yang akurat langsung dari OpenWeatherMap 5-Day/3-Hour Forecast API.
*   **Peta Koordinat Presisi**: Mengambil koordinat pusat desa secara dinamis langsung dari pengaturan profil **Identitas Desa** OpenSID untuk keakuratan cuaca lokal tanpa pengaturan manual.
*   **Desain Dinamis & Responsif**: Menampilkan suhu saat ini dengan ikon ramalan cuaca berukuran besar, deskripsi cuaca, tingkat kelembaban, serta kecepatan angin terkonversi (`km/h`).
*   **Prakiraan 3 Periode**: Menyediakan ramalan cuaca 3 periode (9 jam ke depan) lengkap dengan suhu, ikon, dan waktu prakiraan yang tersusun secara horizontal dan presisi.
*   **Auto-Minimalkan (Collapsible Pill Mode)**: Fitur penciutan cerdas yang mengubah kartu cuaca menjadi status pill ringkas untuk menghemat ruang layar. Secara bawaan (*default*), widget berstatus ciut (*collapsed*) saat pertama kali dibuka.
*   **Visibilitas & Persistence**: Menyimpan status minimized dan tombol visibilitas secara lokal di peramban (*localStorage*) dengan key `weather_minimized_v2` sehingga preferensi pengguna tidak hilang saat halaman dimuat ulang.

---

## 🛠️ Arsitektur Teknologi

*   **Framework Core**: OpenSID / CodeIgniter 3 dengan Laravel Service Container.
*   **Peta Digital**: LeafletJS v1.9.4 & Leaflet.markercluster.
*   **Streaming Engine**: Hls.js (untuk protokol HLS/M3U8), Dukungan Embed YouTube, dan IFrame Umum.
*   **Penyedia Cuaca**: OpenWeatherMap 5-Day/3-Hour Forecast API.
*   **Manajemen Aset**: Dynamic Asset Replicator (Auto-publishing aset modul dari `Assets/` ke public `assets/modules/cctv/` pada saat booting).
*   **Desain Antarmuka**: Tailwind CSS Kompilasi Offline (Frontend) & AdminLTE Bootstrap v3 (Backend).
*   **Ikonografi**: FontAwesome v6.4.0 (Akses ke ribuan ikon modern).
*   **Data Transport**: AJAX, Datatables Server-Side, & RESTful JSON endpoints.

---

## 📦 Manajemen Aset Mandiri (Plug-and-Play)

Untuk menjamin modul ini **100% plug-and-play** dan tidak mengotori atau memodifikasi direktori global OpenSID Anda, modul ini dirancang dengan arsitektur mandiri:
*   Semua file aset (seperti kompilasi offline `tailwind.min.css`) disimpan di dalam repositori modul di folder `Assets/`.
*   Pada saat modul dijalankan pertama kali di lingkungan manapun (termasuk server produksi), `PetaCCTVServiceProvider` secara otomatis mendeteksi dan mereplikasi file-file tersebut ke folder public `assets/modules/cctv/css/` dengan aman.
*   Hal ini menyelesaikan masalah purging sistem Tailwind CSS di server produksi Anda, menjamin keutuhan kode saat modul dipindahkan, dan membuat modul ini aman dipasang/dicopot sewaktu-waktu tanpa meninggalkan sampah berkas.

---

## 📁 Struktur Direktori

```bash
Modules/PetaCCTV/
├── 1_cctv_setup_manual.sql    # SQL dasar untuk inisialisasi tabel & pengaturan awal
├── Assets/                    # Aset Mandiri Modul (Self-Contained)
│   └── css/
│       └── tailwind.min.css   # Kompilasi offline stylesheet Tailwind CSS
├── Config/                    # Konfigurasi Modul
├── Database/
│   ├── Migrations/            # Migrasi Skema Database
│   └── Seeders/               # Data Awal / Contoh Kategori & Kamera
├── Http/
│   └── Controllers/
│       ├── BackEnd/
│       │   └── CctvAdminController.php # Kontroler Administrasi (Backend)
│       └── FrontEnd/
│           └── CctvController.php      # Kontroler Peta Publik (Frontend)
├── Models/
│   ├── CctvCamera.php         # Model Data Kamera & Tempat
│   └── CctvCategory.php       # Model Data Kategori Tempat
├── Providers/
│   └── PetaCCTVServiceProvider.php # Booting & Replikator Aset Mandiri Otomatis
├── Views/
│   ├── backend/
│   │   └── camera/
│   │       ├── form.blade.php  # Form Tambah/Ubah Kamera & Tempat (Interactive Picker)
│   │       └── index.blade.php # Daftar Kamera & Dashboard Admin
│   └── frontend/
│       ├── map.blade.php      # Peta Publik GIS & CCTV Utama
│       └── partials/          # Sub-views modular untuk kebersihan kode
│           ├── header.blade.php
│           ├── modal.blade.php
│           ├── scripts.blade.php
│           ├── toolbar.blade.php
│           └── weather.blade.php # Widget cuaca desa modular
└── README.md                  # Dokumentasi Modul (Bahasa Indonesia)
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

### 📍 Menentukan Koordinat Menggunakan Interactive Picker
1. Masuk ke halaman **Tambah Kamera/Tempat** atau **Edit**.
2. Pada bagian kanan formulir, Anda akan melihat panel **Peta Mini GIS**.
3. Cari lokasi desa Anda di dalam peta tersebut.
4. **Klik langsung** pada titik peta atau **seret (drag) penanda merah** ke bangunan/lokasi tujuan.
5. Kolom `Latitude` dan `Longitude` di atas peta akan terisi secara otomatis dengan presisi tinggi.
6. Klik **Simpan Data Kamera** untuk menyimpan koordinat secara instan tanpa perlu menyalin manual dari situs lain!

---

## 📌 Rencana Pengembangan Selanjutnya (Roadmap)
Untuk masa mendatang, modul ini sangat direkomendasikan untuk ditambahkan:
1. **"Rute Navigasi" Button**: Menambahkan tombol penunjuk arah di popup marker publik yang terintegrasi langsung dengan rute Google Maps.
2. **Pemberitahuan Darurat (Alert Tag)**: Menambahkan penanda darurat berkedip jika terjadi bencana alam, jalan ditutup, atau kegiatan warga.
