<?php

/*
 *
 * File ini bagian dari:
 *
 * Modul Peta CCTV untuk OpenSID
 *
 * Modul ini dikembangkan untuk menambah fitur aplikasi OpenSID
 *
 * @package   Modul Peta CCTV untuk OpenSID
 * @author    Akmal Fadli
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 *
 */

namespace Modules\PetaCCTV\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\PetaCCTV\Models\CctvCategory;
use Modules\PetaCCTV\Models\CctvCamera;

class CctvSeeder extends Seeder
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
            $category = CctvCategory::firstOrCreate([
                'config_id' => $configId,
                'name' => $catName,
            ]);
            $insertedCategories[$catName] = $category->id;
        }

        // Add a sample active camera if none exists
        if (CctvCamera::count() === 0) {
            CctvCamera::create([
                'config_id' => $configId,
                'name' => 'Simpang Kantor Desa',
                'description' => 'Kamera pengawas persimpangan jalan utama depan Kantor Desa Perwira.',
                'latitude' => -7.382046,
                'longitude' => 109.364406,
                // A publicly available public test HLS stream
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'stream_type' => 'hls',
                'thumbnail' => null,
                'category_id' => $insertedCategories['Kantor Desa'] ?? null,
                'is_public' => 1,
                'is_active' => 1,
                'status' => 'online',
                'last_online_at' => now(),
            ]);
            
            CctvCamera::create([
                'config_id' => $configId,
                'name' => 'Live Alun-alun Purbalingga (YouTube)',
                'description' => 'Uji coba livestream YouTube kawasan publik Kabupaten.',
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
    }
}
