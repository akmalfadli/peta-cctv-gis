<?php

/*
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
 */

use Modules\PetaGIS\Models\GisCamera;
use Modules\PetaGIS\Models\GisCategory;
use Modules\PetaGIS\Models\GisPembangunan;
use App\Models\SettingAplikasi;

defined('BASEPATH') || exit('No direct script access allowed');

class GisPublicController extends WebModulController
{
    public $moduleName = 'PetaGIS';
    public $modul_ini = 'gis';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Tampilkan halaman peta publik GIS.
     */
    public function index()
    {
        $categories = GisCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        // Self-heal/Bootstrap weather configurations if missing
        $apiKey = SettingAplikasi::where('key', 'openweathermap_api_key')->first();
        if (!$apiKey) {
            SettingAplikasi::create([
                'config_id' => identitas('id'),
                'key' => 'openweathermap_api_key',
                'value' => '',
                'judul' => 'OpenWeatherMap API Key',
                'keterangan' => 'API Key dari OpenWeatherMap untuk menampilkan informasi cuaca desa.',
                'jenis' => 'text',
                'kategori' => 'PetaGIS',
            ]);
        }

        $statusSetting = SettingAplikasi::where('key', 'openweathermap_status')->first();
        if (!$statusSetting) {
            SettingAplikasi::create([
                'config_id' => identitas('id'),
                'key' => 'openweathermap_status',
                'value' => '0',
                'judul' => 'Status Weather Widget',
                'keterangan' => 'Aktifkan/Nonaktifkan widget informasi cuaca di peta GIS.',
                'jenis' => 'boolean',
                'kategori' => 'PetaGIS',
            ]);
        }

        return view('gis::frontend.map', [
            'categories' => $categories,
            'desa' => $desa,
            'title' => 'Peta GIS & Pembangunan ' . (identitas('nama_desa') ? ucwords(setting('sebutan_desa')) . ' ' . ucwords(identitas('nama_desa')) : 'Desa'),
            'weather_enabled' => setting('openweathermap_status') === '1' && !empty(setting('openweathermap_api_key')),
        ]);
    }

    /**
     * API: Dapatkan data semua kamera yang aktif dan publik.
     */
    public function getCamerasApi()
    {
        $filters = [
            'search' => ci()->input->get('search'),
            'category_id' => ci()->input->get_post('category_id'),
            'status' => ci()->input->get_post('status'),
        ];

        $cameras = GisCamera::with('category')
            ->active()
            ->public()
            ->filter($filters)
            ->get()
            ->map(function ($cam) {
                $thumbUrl = null;
                if ($cam->thumbnail) {
                    $thumbUrl = base_url('shared/gis/' . $cam->thumbnail);
                }

                return [
                    'id' => $cam->id,
                    'name' => $cam->name,
                    'description' => $cam->description,
                    'latitude' => (float) $cam->latitude,
                    'longitude' => (float) $cam->longitude,
                    'stream_url' => $cam->stream_url,
                    'stream_type' => $cam->stream_type,
                    'thumbnail' => $thumbUrl,
                    'category' => $cam->category ? $cam->category->name : 'Tanpa Kategori',
                    'status' => $cam->status,
                    'last_online_at' => $cam->last_online_at ? $cam->last_online_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return json($cameras);
    }

    /**
     * API: Dapatkan rincian spesifik satu kamera.
     */
    public function getCameraDetailApi($id)
    {
        $cam = GisCamera::with('category')
            ->active()
            ->public()
            ->find($id);

        if (!$cam) {
            return json(['error' => 'Kamera tidak ditemukan atau berstatus privat.'], 404);
        }

        $thumbUrl = null;
        if ($cam->thumbnail) {
            $thumbUrl = base_url('shared/gis/' . $cam->thumbnail);
        }

        return json([
            'id' => $cam->id,
            'name' => $cam->name,
            'description' => $cam->description,
            'latitude' => (float) $cam->latitude,
            'longitude' => (float) $cam->longitude,
            'stream_url' => $cam->stream_url,
            'stream_type' => $cam->stream_type,
            'thumbnail' => $thumbUrl,
            'category' => $cam->category ? $cam->category->name : 'Tanpa Kategori',
            'status' => $cam->status,
            'last_online_at' => $cam->last_online_at ? $cam->last_online_at->format('Y-m-d H:i:s') : null,
        ]);
    }

    /**
     * API: Dapatkan data semua pembangunan desa.
     */
    public function getPembangunansApi()
    {
        $filters = [
            'search' => ci()->input->get('search'),
            'type' => ci()->input->get('type'),
            'kategori' => ci()->input->get('kategori'),
            'tahun_anggaran' => ci()->input->get('tahun_anggaran'),
        ];

        $pembangunans = GisPembangunan::filter($filters)
            ->get()
            ->map(function ($pem) {
                $photoUrl = null;
                if ($pem->photo) {
                    $photoUrl = base_url('shared/gis/' . $pem->photo);
                }

                return [
                    'id' => $pem->id,
                    'jenis_kegiatan' => $pem->jenis_kegiatan,
                    'kategori' => $pem->kategori,
                    'tahun_anggaran' => $pem->tahun_anggaran,
                    'sumber_dana' => $pem->sumber_dana,
                    'anggaran' => (float) $pem->anggaran,
                    'anggaran_formatted' => 'Rp ' . number_format($pem->anggaran, 0, ',', '.'),
                    'lokasi' => $pem->lokasi,
                    'volume' => $pem->volume,
                    'pelaksana' => $pem->pelaksana,
                    'photo' => $photoUrl,
                    'type' => $pem->type,
                    'latitude' => $pem->latitude ? (float) $pem->latitude : null,
                    'longitude' => $pem->longitude ? (float) $pem->longitude : null,
                    'coordinates' => $pem->coordinates ? json_decode($pem->coordinates, true) : null,
                ];
            });

        return json($pembangunans);
    }

    /**
     * API Proxy: Dapatkan ramalan cuaca desa tanpa mengekspos API Key ke client.
     */
    public function getWeatherProxy()
    {
        if (!ci()->input->is_ajax_request()) {
            return json(['error' => 'Akses langsung tidak diperbolehkan.'], 403);
        }

        $desa = identitas();
        $lat = !empty($desa->lat) ? $desa->lat : '-7.382046';
        $lon = !empty($desa->lng) ? $desa->lng : '109.364406';
        $apiKey = setting('openweathermap_api_key') ?: '';

        if (empty($apiKey)) {
            return json(['error' => 'OpenWeatherMap API Key belum dikonfigurasi di pengaturan Admin GIS.'], 400);
        }

        $url = "https://api.openweathermap.org/data/2.5/forecast?lat=" . urlencode($lat) . "&lon=" . urlencode($lon) . "&appid=" . urlencode($apiKey) . "&units=metric&lang=id";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return json(['error' => 'Gagal mengambil data dari OpenWeatherMap.', 'code' => $httpCode], 502);
        }

        header('Content-Type: application/json');
        echo $response;
        exit;
    }
}
