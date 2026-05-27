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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SettingAplikasi;
use Modules\PetaGIS\Models\GisCamera;
use Modules\PetaGIS\Models\GisCategory;

defined('BASEPATH') || exit('No direct script access allowed');

class GisCameraAdminController extends AdminModulController
{
    public $moduleName = 'PetaGIS';
    public $modul_ini = 'gis';
    public $sub_modul_ini = 'gis-camera';

    public function __construct()
    {
        parent::__construct();
        isCan('b', $this->modul_ini);
        $this->ensureUploadDirectory();
    }

    /**
     * Tampilkan halaman daftar kamera CCTV.
     */
    public function index()
    {
        $categories = GisCategory::orderBy('name', 'asc')->get();

        // Get basic count statistics
        $stats = [
            'total' => GisCamera::count(),
            'online' => GisCamera::where('status', 'online')->active()->count(),
            'offline' => GisCamera::where('status', 'offline')->active()->count(),
            'inactive' => GisCamera::where('is_active', 0)->count(),
        ];

        return view('gis::backend.camera.index', [
            'categories' => $categories,
            'stats' => $stats,
            'title' => 'Daftar Kamera CCTV',
        ]);
    }

    /**
     * DataTables server-side untuk kamera CCTV.
     */
    public function datatables()
    {
        $filters = [
            'search' => ci()->input->get_post('search'),
            'category_id' => ci()->input->get_post('category_id'),
            'status' => ci()->input->get_post('status'),
            'is_active' => ci()->input->get_post('is_active'),
            'is_public' => ci()->input->get_post('is_public'),
        ];

        $query = GisCamera::with('category')->filter($filters);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('thumbnail_view', function ($row) {
                if ($row->thumbnail) {
                    return '<img src="' . base_url('shared/gis/' . $row->thumbnail) . '" alt="Thumbnail" class="img-thumbnail" style="max-height: 50px; max-width: 80px;">';
                }
                return '<span class="label label-default">No Image</span>';
            })
            ->editColumn('stream_type', static fn($row) => strtoupper($row->stream_type))
            ->addColumn('visibility', function ($row) {
                $checked = $row->is_public ? 'checked' : '';
                return '
                    <input type="checkbox" 
                           class="toggle-public" 
                           data-id="' . $row->id . '" 
                           data-toggle="toggle" 
                           data-onstyle="success" 
                           data-offstyle="danger" 
                           data-on="Publik" 
                           data-off="Privat" 
                           data-size="mini" 
                           data-width="60"
                           ' . $checked . '>
                ';
            })
            ->addColumn('active_status', function ($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '
                    <input type="checkbox" 
                           class="toggle-active" 
                           data-id="' . $row->id . '" 
                           data-toggle="toggle" 
                           data-onstyle="success" 
                           data-offstyle="danger" 
                           data-on="Aktif" 
                           data-off="Nonaktif" 
                           data-size="mini" 
                           data-width="60"
                           ' . $checked . '>
                ';
            })
            ->addColumn('health_status', function ($row) {
                if (!$row->is_active) {
                    return '<span class="label label-default">Inactive</span>';
                }
                if ($row->status === 'online') {
                    return '<span class="label label-success"><i class="fa fa-circle"></i> Online</span>';
                }
                return '<span class="label label-danger"><i class="fa fa-circle"></i> Offline</span>';
            })
            ->addColumn('aksi', function ($row) {
                $html = '<a href="' . ci_route('gis_camera.edit', $row->id) . '" class="btn btn-primary btn-xs" title="Edit Kamera"><i class="fa fa-pencil"></i></a> ';
                $html .= '<a href="' . ci_route('gis_camera.delete', $row->id) . '" class="btn btn-danger btn-xs btn-delete-cctv" title="Hapus"><i class="fa fa-trash"></i></a>';
                return $html;
            })
            ->rawColumns(['thumbnail_view', 'visibility', 'active_status', 'health_status', 'aksi'])
            ->make();
    }

    /**
     * Halaman tambah kamera CCTV.
     */
    public function create()
    {
        isCan('u', $this->modul_ini);

        $categories = GisCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        return view('gis::backend.camera.form', [
            'camera' => null,
            'categories' => $categories,
            'desa' => $desa,
            'title' => 'Tambah Kamera CCTV Baru',
        ]);
    }

    /**
     * Simpan kamera baru.
     */
    public function store()
    {
        isCan('u', $this->modul_ini);

        $name = trim(ci()->input->post('name') ?: '');
        $description = trim(ci()->input->post('description') ?: '');
        $latitude = ci()->input->post('latitude');
        $longitude = ci()->input->post('longitude');
        $stream_url = trim(ci()->input->post('stream_url') ?: '');
        $stream_type = $stream_url !== '' ? ci()->input->post('stream_type') : null;
        $category_id = ci()->input->post('category_id') ?: null;
        $is_public = ci()->input->post('is_public') ? 1 : 0;
        $is_active = ci()->input->post('is_active') ? 1 : 0;

        // Validations
        if (empty($name)) {
            set_session('error', 'Nama kamera/tempat tidak boleh kosong.');
            return redirect('gis_camera/create');
        }
        if ($latitude === '' || $longitude === '') {
            set_session('error', 'Koordinat peta lokasi tidak boleh kosong.');
            return redirect('gis_camera/create');
        }

        DB::beginTransaction();

        try {
            // Handle Thumbnail Upload
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnail = $this->handleUpload($_FILES['thumbnail']);
            }

            GisCamera::create([
                'config_id' => identitas('id'),
                'name' => $name,
                'description' => $description,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'stream_url' => $stream_url,
                'stream_type' => $stream_type ?: '',
                'thumbnail' => $thumbnail,
                'category_id' => $category_id,
                'is_public' => $is_public,
                'is_active' => $is_active,
                'status' => 'online', // default online until health check fails
                'last_online_at' => now(),
            ]);

            DB::commit();
            set_session('success', 'Kamera CCTV "' . $name . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menambahkan kamera: ' . $e->getMessage());
            return redirect('gis_camera/create');
        }

        return redirect('gis_camera');
    }

    /**
     * Halaman edit kamera CCTV.
     */
    public function edit($id)
    {
        isCan('u', $this->modul_ini);

        $camera = GisCamera::findOrFail($id);
        $categories = GisCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        return view('gis::backend.camera.form', [
            'camera' => $camera,
            'categories' => $categories,
            'desa' => $desa,
            'title' => 'Edit Kamera CCTV: ' . $camera->name,
        ]);
    }

    /**
     * Perbarui kamera.
     */
    public function update($id)
    {
        isCan('u', $this->modul_ini);

        $camera = GisCamera::findOrFail($id);

        $name = trim(ci()->input->post('name') ?: '');
        $description = trim(ci()->input->post('description') ?: '');
        $latitude = ci()->input->post('latitude');
        $longitude = ci()->input->post('longitude');
        $stream_url = trim(ci()->input->post('stream_url') ?: '');
        $stream_type = $stream_url !== '' ? ci()->input->post('stream_type') : null;
        $category_id = ci()->input->post('category_id') ?: null;
        $is_public = ci()->input->post('is_public') ? 1 : 0;
        $is_active = ci()->input->post('is_active') ? 1 : 0;

        // Validations
        if (empty($name)) {
            set_session('error', 'Nama kamera/tempat tidak boleh kosong.');
            return redirect('gis_camera/edit/' . $id);
        }
        if ($latitude === '' || $longitude === '') {
            set_session('error', 'Koordinat peta lokasi tidak boleh kosong.');
            return redirect('gis_camera/edit/' . $id);
        }

        DB::beginTransaction();

        try {
            $thumbnail = $camera->thumbnail;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                // Delete old thumbnail if exists
                if ($thumbnail && file_exists(FCPATH . 'shared/gis/' . $thumbnail)) {
                    @unlink(FCPATH . 'shared/gis/' . $thumbnail);
                }
                $thumbnail = $this->handleUpload($_FILES['thumbnail']);
            }

            $camera->update([
                'name' => $name,
                'description' => $description,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'stream_url' => $stream_url,
                'stream_type' => $stream_type ?: '',
                'thumbnail' => $thumbnail,
                'category_id' => $category_id,
                'is_public' => $is_public,
                'is_active' => $is_active,
            ]);

            DB::commit();
            set_session('success', 'Kamera CCTV "' . $name . '" berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal diperbarui: ' . $e->getMessage());
            return redirect('gis_camera/edit/' . $id);
        }

        return redirect('gis_camera');
    }

    /**
     * Hapus kamera CCTV.
     */
    public function delete($id)
    {
        isCan('h', $this->modul_ini);

        $camera = GisCamera::findOrFail($id);

        DB::beginTransaction();

        try {
            $name = $camera->name;

            // Delete thumbnail file
            if ($camera->thumbnail && file_exists(FCPATH . 'shared/gis/' . $camera->thumbnail)) {
                @unlink(FCPATH . 'shared/gis/' . $camera->thumbnail);
            }

            $camera->delete();

            DB::commit();
            set_session('success', 'Kamera CCTV "' . $name . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menghapus kamera: ' . $e->getMessage());
        }

        return redirect('gis_camera');
    }

    /**
     * AJAX Toggle Active.
     */
    public function toggleActive($id)
    {
        isCan('u', $this->modul_ini);

        $camera = GisCamera::findOrFail($id);
        $camera->is_active = $camera->is_active ? 0 : 1;
        $camera->save();

        return json(['success' => true, 'is_active' => $camera->is_active]);
    }

    /**
     * AJAX Toggle Public.
     */
    public function togglePublic($id)
    {
        isCan('u', $this->modul_ini);

        $camera = GisCamera::findOrFail($id);
        $camera->is_public = $camera->is_public ? 0 : 1;
        $camera->save();

        return json(['success' => true, 'is_public' => $camera->is_public]);
    }

    /**
     * Health Monitoring Trigger (Cron-compatible).
     * Sends lightweight HEAD requests to active camera stream URLs to check status.
     */
    public function healthCheck()
    {
        $isCli = is_cli();
        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);

        if (!$isCli && !$isLocalhost) {
            isCan('b', $this->modul_ini);
        }

        $cameras = GisCamera::active()->get();
        $checked = 0;
        $online = 0;

        foreach ($cameras as $camera) {
            if (empty($camera->stream_url)) {
                // Places with only coordinates (no stream URL) are always online
                $camera->update([
                    'status' => 'online',
                    'last_online_at' => now(),
                ]);
                $online++;
                $checked++;
                continue;
            }

            $checked++;
            $status = $this->pingUrl($camera->stream_url, $camera->stream_type);

            if ($status === 'online') {
                $camera->update([
                    'status' => 'online',
                    'last_online_at' => now(),
                ]);
                $online++;
            } else {
                $camera->update([
                    'status' => 'offline',
                ]);
            }
        }

        $res = [
            'success' => true,
            'message' => "Health check finished. Checked: {$checked}, Online: {$online}, Offline: " . ($checked - $online),
        ];

        if ($isCli) {
            echo $res['message'] . PHP_EOL;
            exit;
        }

        if (ci()->input->is_ajax_request()) {
            return json($res);
        }

        set_session('success', $res['message']);
        return redirect('gis_camera');
    }

    /**
     * Verification Helper: cURL Ping URL.
     */
    private function pingUrl(string $url, string $type): string
    {
        if ($type === 'youtube' || str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            $url = 'https://www.youtube.com';
        }

        if ($type === 'iframe' && preg_match('/src="([^"]+)"/', $url, $match)) {
            $url = $match[1];
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 400) {
            return 'online';
        }

        if ($httpCode === 405) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RANGE, '0-100');

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 400) {
                return 'online';
            }
        }

        return 'offline';
    }

    /**
     * Helpers: Ensure shared/gis directory exists.
     */
    private function ensureUploadDirectory(): void
    {
        $path = FCPATH . 'shared/gis/';
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            file_put_contents($path . 'index.html', '<html><body bgcolor="#ffffff"></body></html>');
        }
    }

    /**
     * Helpers: Upload File.
     */
    private function handleUpload(array $file): string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'gis_' . uniqid() . '.' . strtolower($ext);
        $target = FCPATH . 'shared/gis/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $fileName;
        }

        throw new \Exception('Gagal memindahkan file.');
    }

    /**
     * Halaman Pengaturan Cuaca OpenWeatherMap.
     */
    public function settings()
    {
        isCan('b', $this->modul_ini);

        if (ci()->input->method() === 'post') {
            isCan('u', $this->modul_ini);

            $apiKey = trim(ci()->input->post('weather_api_key') ?: '');
            $status = ci()->input->post('weather_enabled') ? '1' : '0';

            DB::beginTransaction();

            try {
                SettingAplikasi::where('key', 'openweathermap_api_key')
                    ->update(['value' => $apiKey]);

                SettingAplikasi::where('key', 'openweathermap_status')
                    ->update(['value' => $status]);

                DB::commit();

                cache()->forget('setting_aplikasi');
                if (class_exists('App\Models\SettingAplikasi')) {
                    (new SettingAplikasi())->flushQueryCache();
                }

                set_session('success', 'Pengaturan integrasi cuaca berhasil disimpan.');
            } catch (\Exception $e) {
                DB::rollBack();
                set_session('error', 'Gagal menyimpan: ' . $e->getMessage());
            }

            return redirect('gis_camera/settings');
        }

        $desa = identitas();

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

        return view('gis::backend.settings.index', [
            'weather_api_key' => setting('openweathermap_api_key') ?: '',
            'weather_enabled' => setting('openweathermap_status') === '1',
            'desa' => $desa,
            'title' => 'Pengaturan Integrasi Cuaca',
        ]);
    }
}
