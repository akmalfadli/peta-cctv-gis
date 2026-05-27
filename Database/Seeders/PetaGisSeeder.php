<?php

/*
 *
 * File ini bagian dari:
 *
 * Modul Peta GIS untuk OpenSID
 *
 * Modul ini dikembangkan untuk menambah fitur aplikasi OpenSID
 *
 * @package   Modul Peta GIS untuk OpenSID
 * @author    Akmal Fadli
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 *
 */

namespace Modules\PetaGIS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\PetaGIS\Models\GisCategory;
use Modules\PetaGIS\Models\GisCamera;
use Modules\PetaGIS\Models\GisPembangunan;

class PetaGisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
     {
        $configId = DB::table('config')->value('id') ?: 1;

        $categories = [
            'Kantor Desa',
            'Jalan Raya',
            'Wisata',
            'Pasar',
            'Parkir',
            'Sungai',
        ];

        $insertedCategories = [];

        foreach ($categories as $catName) {
            $category = GisCategory::firstOrCreate([
                'config_id' => $configId,
                'name' => $catName,
            ]);
            $insertedCategories[$catName] = $category->id;
        }

        // Add a sample active camera if none exists
        if (GisCamera::count() === 0) {
            GisCamera::create([
                'config_id' => $configId,
                'name' => 'Simpang Kantor Desa',
                'description' => 'Kamera pengawas persimpangan jalan utama depan Kantor Desa Perwira.',
                'latitude' => -7.382046,
                'longitude' => 109.364406,
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'stream_type' => 'hls',
                'thumbnail' => null,
                'category_id' => $insertedCategories['Kantor Desa'] ?? null,
                'is_public' => 1,
                'is_active' => 1,
                'status' => 'online',
                'last_online_at' => now(),
            ]);
            
            GisCamera::create([
                'config_id' => $configId,
                'name' => 'Kawasan Wisata Desa',
                'description' => 'Kamera pemantauan arus wisatawan pada pintu masuk agrowisata.',
                'latitude' => -7.388880,
                'longitude' => 109.361110,
                'stream_url' => 'https://www.youtube.com/embed/jfKfPfyJRdk',
                'stream_type' => 'youtube',
                'thumbnail' => null,
                'category_id' => $insertedCategories['Wisata'] ?? null,
                'is_public' => 1,
                'is_active' => 1,
                'status' => 'online',
                'last_online_at' => now(),
            ]);
        }

        // Add sample Pembangunan Desa projects if none exists
        if (GisPembangunan::count() === 0) {
            // 1. Pendidikan: Building Project (Gedung PAUD)
            GisPembangunan::create([
                'config_id' => $configId,
                'jenis_kegiatan' => 'Pembangunan Gedung PAUD Tunas Bangsa',
                'kategori' => 'pendidikan',
                'tahun_anggaran' => '2026',
                'type' => 'building',
                'sumber_dana' => 'Dana Desa (DD)',
                'anggaran' => 125000000,
                'lokasi' => 'RT 01 RW 03 Dusun I',
                'volume' => '1 Unit (8m x 12m)',
                'pelaksana' => 'Tim Pelaksana Kegiatan (TPK) Desa',
                'latitude' => -7.383120,
                'longitude' => 109.365510,
                'coordinates' => null,
                'photo' => null
            ]);

            // 2. Infrastruktur: Road Project (Pondasi/Pengaspalan)
            $roadCoordinates = [
                [-7.382046, 109.364406],
                [-7.381200, 109.364800],
                [-7.380500, 109.365300],
                [-7.379800, 109.366000]
            ];

            GisPembangunan::create([
                'config_id' => $configId,
                'jenis_kegiatan' => 'Pengaspalan Jalan Lingkungan Dusun II',
                'kategori' => 'infrastruktur',
                'tahun_anggaran' => '2026',
                'type' => 'road',
                'sumber_dana' => 'Alokasi Dana Desa (ADD)',
                'anggaran' => 185000000,
                'lokasi' => 'Dusun II (RT 03 ke RT 05)',
                'volume' => 'P 550m x L 3m',
                'pelaksana' => 'Lembaga Pemberdayaan Masyarakat (LPM)',
                'latitude' => null,
                'longitude' => null,
                'coordinates' => json_encode($roadCoordinates),
                'photo' => null
            ]);

            // 3. Kesehatan: Building Project (Posyandu)
            GisPembangunan::create([
                'config_id' => $configId,
                'jenis_kegiatan' => 'Pembangunan Gedung Posyandu Melati',
                'kategori' => 'kesehatan',
                'tahun_anggaran' => '2026',
                'type' => 'building',
                'sumber_dana' => 'Dana Desa (DD)',
                'anggaran' => 95000000,
                'lokasi' => 'RT 04 RW 02 Dusun II',
                'volume' => '1 Unit (6m x 8m)',
                'pelaksana' => 'Tim Pelaksana Kegiatan (TPK)',
                'latitude' => -7.385500,
                'longitude' => 109.362200,
                'coordinates' => null,
                'photo' => null
            ]);

            // 4. Ekonomi: Building Project (Pasar Desa)
            GisPembangunan::create([
                'config_id' => $configId,
                'jenis_kegiatan' => 'Penyertaan Modal & Kios BUMDes Perwira',
                'kategori' => 'ekonomi',
                'tahun_anggaran' => '2026',
                'type' => 'building',
                'sumber_dana' => 'Bagi Hasil Pajak & Retribusi',
                'anggaran' => 150000000,
                'lokasi' => 'Kawasan Pasar Desa Perwira',
                'volume' => '5 Kios Baru',
                'pelaksana' => 'Pengurus BUMDes Karya Perwira',
                'latitude' => -7.380000,
                'longitude' => 109.360000,
                'coordinates' => null,
                'photo' => null
            ]);

            // 5. Lingkungan & Kebencanaan: Road/Line Project (Tanggul Banjir / Saluran Drainase Induk)
            $drainageCoordinates = [
                [-7.387000, 109.366000],
                [-7.388000, 109.367000],
                [-7.389000, 109.368000]
            ];

            GisPembangunan::create([
                'config_id' => $configId,
                'jenis_kegiatan' => 'Pembangunan Talud & Saluran Drainase Mitigasi Banjir',
                'kategori' => 'lingkungan',
                'tahun_anggaran' => '2026',
                'type' => 'road',
                'sumber_dana' => 'Dana Desa (DD)',
                'anggaran' => 110000000,
                'lokasi' => 'Bantaran Sungai Dusun III',
                'volume' => 'Panjang 250 meter',
                'pelaksana' => 'Tim Pelaksana Kegiatan (TPK)',
                'latitude' => null,
                'longitude' => null,
                'coordinates' => json_encode($drainageCoordinates),
                'photo' => null
            ]);
        }
    }
}
