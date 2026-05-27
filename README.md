# 🗺️ Modul Peta GIS & Pembangunan Desa (PetaGIS)

Modul **PetaGIS** adalah platform Sistem Informasi Geografis (GIS) terpadu untuk lingkungan **OpenSID** yang dirancang khusus untuk menampilkan lokasi kamera pemantauan (CCTV) langsung (live streaming), penandaan fasilitas umum, serta **Pemetaan Proyek Pembangunan Desa (Pembangunan Desa)**—seperti Jalan (Garis/Polyline) dan Gedung/Bangunan (Titik/Marker).

Modul ini dirancang agar cepat, mandiri (plug-and-play), aman, dan mudah dikelola oleh perangkat desa secara digital tanpa ketergantungan pada vendor eksternal.

---

## 🚀 Fitur Utama

### 1. 🗺️ Visualisasi GIS Interaktif (Frontend)
*   **Peta Layar Penuh (LeafletJS)**: Antarmuka peta yang cepat dan responsif dengan fitur pengklasteran marker (*MarkerCluster*) untuk performa optimal pada perangkat seluler maupun desktop.
*   **Pemetaan Pembangunan Desa (Roads & Buildings)**:
    *   **Tipe Garis (Road / Jalan)**: Menampilkan polylines rute pembangunan jalan desa dengan warna sky-blue yang kontras.
    *   **Hover Glow Effect**: Saat kursor menyorot garis jalan, ketebalan dan warnanya berubah dinamis (bercahaya emas/oranye) untuk memberi penekanan visual.
    *   **Tipe Titik (Building / Gedung)**: Menampilkan marker khusus bergambar gedung/bangunan untuk proyek fisik sekolah, kantor, posyandu, dsb.
*   **Floating Detail Drawer (Slide-over Panel)**: Menampilkan informasi lengkap pembangunan desa (Jenis Kegiatan, Anggaran, Sumber Dana, Lokasi, Volume, Pelaksana, dan Foto Dokumentasi) di panel melayang premium yang meluncur anggun dari sisi kanan peta tanpa menutup ruang peta utama.
*   **Layer Controls (Lapisan Peta)**: Tombol filter di pojok kanan atas toolbar yang memudahkan pengunjung menyembunyikan atau menampilkan lapisan Kamera CCTV dan Pembangunan Desa secara terpisah.
*   **Multi-Tileset Switcher**: Memungkinkan pengguna beralih jenis peta antara peta jalan (OSM), peta satelit, dan peta minimalis abu-abu (*gray*).

### 2. ⚙️ Manajemen Administrasi Premium (Backend)
*   **Interactive Location Picker (Gis Form Map)**: Dilengkapi peta interaktif mini LeafletJS pada formulir Tambah & Edit Lokasi Kamera. Admin cukup mengklik peta atau menyeret penanda (*draggable marker*) merah untuk mengisi koordinat secara otomatis.
*   **Interactive Polyline Editor (Road Drawer)**:
    *   Formulir input pembangunan desa yang mendeteksi pilihan tipe proyek secara dinamis.
    *   Jika tipe **Jalan** terpilih, peta mini berubah menjadi kanvas menggambar polyline interaktif. Admin cukup mengklik peta berurutan untuk membentuk rute jalan, lengkap dengan tombol **Batal Titik Terakhir** (Undo) dan **Hapus Semua Titik** (Clear).
    *   Jika tipe **Gedung** terpilih, peta mini beralih menjadi koordinat tunggal dengan marker merah seret-lepas.
*   **Dashboard Statistik Telemetri**: Menampilkan kartu statistik ringkas: Total Proyek, Pembangunan Jalan, Gedung & Bangunan, serta Total Alokasi Anggaran.
*   **Sistem Telemetri Cek Kesehatan Otomatis (Health Check)**: Fitur pengecekan koneksi streaming yang melakukan ping berkala ke server CCTV. Kamera tanpa stream secara cerdas dilewati (*bypassed*) agar tetap ditandai "Online" tanpa memperlambat performa server.
*   **Pencarian & Penyaringan Server-Side**: Menggunakan integrasi *DataTables* berkinerja tinggi untuk memilah data berdasarkan Kategori, Status Kesehatan, Keaktifan, dan Visibilitas Publik.

### 3. 🌤️ Widget Informasi Cuaca Desa (Weather Widget)
*   **Integrasi OpenWeatherMap API**: Menampilkan ramalan cuaca real-time yang akurat langsung dari OpenWeatherMap 5-Day/3-Hour Forecast API.
*   **Secure API Proxy (Perlindungan Kredensial)**: Menyembunyikan dan mengamankan OpenWeatherMap API Key sepenuhnya dari client-side JavaScript. Semua data cuaca ditarik melalui proxy PHP backend (`gis/weather`) menggunakan cURL yang terlindungi di server-side.
*   **Peta Koordinat Presisi**: Mengambil koordinat pusat desa secara dinamis langsung dari pengaturan profil **Identitas Desa** OpenSID untuk keakuratan cuaca lokal tanpa pengaturan manual.
*   **Auto-Minimalkan (Collapsible Mode)**: Secara bawaan (*default*), widget berstatus ciut (*collapsed*) saat pertama kali dibuka untuk menjaga area fokus utama peta.

---

## 🛠️ Arsitektur Teknologi

*   **Framework Core**: OpenSID / CodeIgniter 3 dengan Laravel Service Container.
*   **Peta Digital**: LeafletJS v1.9.4 & Leaflet.markercluster.
*   **Streaming Engine**: Hls.js (untuk protokol HLS/M3U8), YouTube Embed, dan IFrame Umum.
*   **Penyedia Cuaca**: OpenWeatherMap 5-Day/3-Hour Forecast API.
*   **Manajemen Aset**: Dynamic Asset Replicator (Auto-publishing aset modul dari `Assets/` ke public `assets/modules/gis/` pada saat booting).
*   **Desain Antarmuka**: Tailwind CSS Kompilasi Offline (Frontend) & AdminLTE Bootstrap v3 (Backend).
*   **Ikonografi**: FontAwesome v6.4.0 (Akses ke ribuan ikon modern).
*   **Data Transport**: AJAX, Datatables Server-Side, & RESTful JSON endpoints.

---

## 📦 Manajemen Aset Mandiri (Plug-and-Play)

Untuk menjamin modul ini **100% plug-and-play** dan tidak mengotori atau memodifikasi direktori global OpenSID Anda, modul ini dirancang dengan arsitektur mandiri:
*   Semua file aset (seperti kompilasi offline `tailwind.min.css`) disimpan di dalam repositori modul di folder `Assets/`.
*   Pada saat modul dijalankan pertama kali di lingkungan manapun, `PetaGISServiceProvider` secara otomatis mendeteksi dan mereplikasi file-file tersebut ke folder public `assets/modules/gis/css/` dengan aman.

---

## 📁 Struktur Direktori

```bash
Modules/PetaGIS/
├── 1_gis_setup_manual.sql     # SQL dasar untuk inisialisasi tabel & pengaturan awal
├── Assets/                    # Aset Mandiri Modul (Self-Contained)
│   └── css/
│       └── tailwind.min.css   # Kompilasi offline stylesheet Tailwind CSS
├── Database/
│   ├── Migrations/            # Migrasi Skema Database (Kategori, Kamera, Pembangunan)
│   └── Seeders/               # Data Awal / Contoh Kategori, Kamera, & Proyek Pembangunan
├── Http/
│   └── Controllers/
│       ├── BackEnd/
│       │   ├── GisCameraAdminController.php      # Kontroler CCTV & Kamera (Backend)
│       │   ├── GisCategoryAdminController.php    # Kontroler Kategori (Backend)
│       │   └── GisPembangunanAdminController.php  # Kontroler Pembangunan Desa (Backend)
│       └── FrontEnd/
│           └── GisPublicController.php           # Kontroler Peta GIS Publik (Frontend)
├── Models/
│   ├── GisCamera.php          # Model Data Kamera & Tempat
│   ├── GisCategory.php        # Model Data Kategori GIS
│   └── GisPembangunan.php     # Model Data Pembangunan Desa (Lines & Points)
├── Providers/
│   └── PetaGISServiceProvider.php # Booting & Replikator Aset Mandiri Otomatis
├── Views/
│   ├── backend/
│   │   ├── camera/
│   │   │   ├── form.blade.php  # Form Kamera (Interactive Picker)
│   │   │   └── index.blade.php # Daftar Kamera & Dashboard Admin
│   │   ├── category/
│   │   │   └── index.blade.php # Manajemen Kategori GIS
│   │   └── pembangunan/
│   │       ├── form.blade.php  # Form Pembangunan (Interactive Picker & Polyline Drawer)
│   │       └── index.blade.php # Daftar Pembangunan Desa & Dashboard
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

Modul ini menggunakan tiga tabel utama di dalam database:

### 1. `gis_categories`
Menyimpan nama-nama kategori tempat.
*   `id` (INT, Primary Key)
*   `name` (VARCHAR, e.g. "Kantor Desa", "Sekolah", "Jalan Raya")

### 2. `gis_cameras`
Menyimpan detail CCTV dan Marker Geografis Tempat.
*   `id` (INT, Primary Key)
*   `config_id` (INT, Relasi Identitas Desa)
*   `category_id` (INT, Relasi ke `gis_categories`)
*   `name` (VARCHAR, Nama Lokasi / CCTV)
*   `latitude`, `longitude` (DECIMAL 10,8)
*   `stream_url` (VARCHAR, nullable)
*   `stream_type` (VARCHAR, e.g. `hls`, `youtube`, `iframe`)
*   `thumbnail` (VARCHAR, Gambar Cover)
*   `status` (VARCHAR, `online` / `offline`)

### 3. `gis_pembangunans`
Menyimpan rute pembangunan infrastruktur jalan dan bangunan fasilitas desa.
*   `id` (INT, Primary Key)
*   `jenis_kegiatan` (VARCHAR, Nama Proyek)
*   `sumber_dana` (VARCHAR, e.g. "Dana Desa")
*   `anggaran` (DECIMAL 15,2, Alokasi Rupiah)
*   `lokasi` (VARCHAR, Dusun/RT/RW)
*   `volume` (VARCHAR, Volume/Dimensi Fisik)
*   `pelaksana` (VARCHAR, TPK/LPM)
*   `photo` (VARCHAR, Foto Dokumentasi)
*   `type` (ENUM, `road` / `building`)
*   `latitude`, `longitude` (DECIMAL 10,8, Digunakan jika type `building`)
*   `coordinates` (TEXT, JSON Koordinat Polyline [[lat,lng],[lat,lng]...] jika type `road`)

---

## ⚙️ Petunjuk Operasional & Pemeliharaan

### 📌 Menjalankan Pengecekan Status Stream Otomatis (Cron Job)
Agar status `Online` / `Offline` CCTV terupdate secara otomatis tanpa harus menekan tombol manual di admin, Anda dapat mendaftarkan cron job pada server hosting Anda. 

Jalankan perintah berikut setiap **5 atau 10 menit**:
```bash
*/5 * * * * curl -s http://domain-desa.id/index.php/gis_camera/health_check > /dev/null 2>&1
```
*Sistem telemetri ini secara otomatis mengabaikan marker berlabel **Tanpa Stream** sehingga tidak membebani penggunaan CPU server.*

### 📍 Menentukan Koordinat Menggunakan Interactive Picker
1. Masuk ke halaman **Tambah Kamera/Tempat** atau **Edit**.
2. Pada bagian kanan formulir, Anda akan melihat panel **Peta Mini GIS**.
3. Cari lokasi desa Anda di dalam peta tersebut.
4. **Klik langsung** pada titik peta atau **seret (drag) penanda merah** ke bangunan/lokasi tujuan.
5. Kolom `Latitude` dan `Longitude` di atas peta akan terisi secara otomatis dengan presisi tinggi.

### 🛣️ Menggambar Garis Rute Jalan Desa
1. Tambah data baru di **Pembangunan Desa**.
2. Pilih tipe pembangunan: **Jalan / Jembatan (Garis)**.
3. Di panel kanan, Anda akan disajikan peta kanvas interaktif dan panel alat menggambar.
4. **Klik berturut-turut** pada jalan di peta untuk menggambar rute pengaspalan/pelebaran jalan. Penanda lingkaran putih akan muncul menandai sudut tikungan jalan.
5. Jika salah meletakkan titik, tekan **Batal Titik Terakhir** untuk menghapusnya, atau **Hapus Semua Titik** untuk mengulangi dari awal.
6. Koordinat rute otomatis terserialisasi ke dalam sistem untuk langsung digambar di peta publik.
