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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PetaCCTV\Models\CctvCamera;
use Modules\PetaCCTV\Models\CctvCategory;

defined('BASEPATH') || exit('No direct script access allowed');

class CctvAdminController extends AdminModulController
{
    public $moduleName = 'PetaCCTV';
    public $modul_ini = 'cctv';
    public $sub_modul_ini = 'cctv-admin';

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
        $categories = CctvCategory::orderBy('name', 'asc')->get();

        // Get basic count statistics
        $stats = [
            'total' => CctvCamera::count(),
            'online' => CctvCamera::where('status', 'online')->active()->count(),
            'offline' => CctvCamera::where('status', 'offline')->active()->count(),
            'inactive' => CctvCamera::where('is_active', 0)->count(),
        ];

        return view('cctv::backend.camera.index', [
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

        $query = CctvCamera::with('category')->filter($filters);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('thumbnail_view', function ($row) {
                if ($row->thumbnail) {
                    return '<img src="' . base_url('shared/cctv/' . $row->thumbnail) . '" alt="Thumbnail" class="img-thumbnail" style="max-height: 50px; max-width: 80px;">';
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
                $html = '<a href="' . ci_route('cctv_admin.edit', $row->id) . '" class="btn btn-primary btn-xs" title="Edit Kamera"><i class="fa fa-pencil"></i></a> ';
                $html .= '<a href="' . ci_route('cctv_admin.delete', $row->id) . '" class="btn btn-danger btn-xs btn-delete-cctv" title="Hapus"><i class="fa fa-trash"></i></a>';
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

        $categories = CctvCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        return view('cctv::backend.camera.form', [
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
            return redirect('cctv_admin/create');
        }
        if ($latitude === '' || $longitude === '') {
            set_session('error', 'Koordinat peta lokasi tidak boleh kosong.');
            return redirect('cctv_admin/create');
        }

        DB::beginTransaction();

        try {
            // Handle Thumbnail Upload
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnail = $this->handleUpload($_FILES['thumbnail']);
            }

            CctvCamera::create([
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
            return redirect('cctv_admin/create');
        }

        return redirect('cctv_admin');
    }

    /**
     * Halaman edit kamera CCTV.
     */
    public function edit($id)
    {
        isCan('u', $this->modul_ini);

        $camera = CctvCamera::findOrFail($id);
        $categories = CctvCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        return view('cctv::backend.camera.form', [
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

        $camera = CctvCamera::findOrFail($id);

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
            return redirect('cctv_admin/edit/' . $id);
        }
        if ($latitude === '' || $longitude === '') {
            set_session('error', 'Koordinat peta lokasi tidak boleh kosong.');
            return redirect('cctv_admin/edit/' . $id);
        }

        DB::beginTransaction();

        try {
            $thumbnail = $camera->thumbnail;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                // Delete old thumbnail if exists
                if ($thumbnail && file_exists(FCPATH . 'shared/cctv/' . $thumbnail)) {
                    @unlink(FCPATH . 'shared/cctv/' . $thumbnail);
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
            set_session('error', 'Gagal memperbarui kamera: ' . $e->getMessage());
            return redirect('cctv_admin/edit/' . $id);
        }

        return redirect('cctv_admin');
    }

    /**
     * Hapus kamera CCTV.
     */
    public function delete($id)
    {
        isCan('h', $this->modul_ini);

        $camera = CctvCamera::findOrFail($id);

        DB::beginTransaction();

        try {
            $name = $camera->name;

            // Delete thumbnail file
            if ($camera->thumbnail && file_exists(FCPATH . 'shared/cctv/' . $camera->thumbnail)) {
                @unlink(FCPATH . 'shared/cctv/' . $camera->thumbnail);
            }

            $camera->delete();

            DB::commit();
            set_session('success', 'Kamera CCTV "' . $name . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menghapus kamera: ' . $e->getMessage());
        }

        return redirect('cctv_admin');
    }

    /**
     * AJAX Toggle Active.
     */
    public function toggleActive($id)
    {
        isCan('u', $this->modul_ini);

        $camera = CctvCamera::findOrFail($id);
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

        $camera = CctvCamera::findOrFail($id);
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
        // Don't require strict admin session check if triggered from CLI/local,
        // but verify key/token or require it if not from localhost.
        $isCli = is_cli();
        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);

        if (!$isCli && !$isLocalhost) {
            isCan('b', $this->modul_ini);
        }

        $cameras = CctvCamera::active()->get();
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
        return redirect('cctv_admin');
    }

    /**
     * Verification Helper: cURL Ping URL.
     */
    private function pingUrl(string $url, string $type): string
    {
        // 1) For YouTube embeds, ping YouTube Domain instead of individual streams
        if ($type === 'youtube' || str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            $url = 'https://www.youtube.com';
        }

        // 2) If it's an iframe snippet, extract the src URL
        if ($type === 'iframe' && preg_match('/src="([^"]+)"/', $url, $match)) {
            $url = $match[1];
        }

        // Clean URL scheme
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        // 3) Light Connection cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only (fast)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);          // request timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);   // connection timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Standard HTTP success statuses
        if ($httpCode >= 200 && $httpCode < 400) {
            return 'online';
        }

        // Fallback: If HEAD fails (some NVR streams return 405 Method Not Allowed), retry with standard GET request
        if ($httpCode === 405) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_RANGE, '0-100'); // only fetch first 100 bytes

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
     * Helpers: Ensure shared/cctv directory exists.
     */
    private function ensureUploadDirectory(): void
    {
        $path = FCPATH . 'shared/cctv/';
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            // Put index.html inside to prevent folder directory browsing
            file_put_contents($path . 'index.html', '<html><body bgcolor="#ffffff"></body></html>');
        }
    }

    /**
     * Helpers: Upload File.
     */
    private function handleUpload(array $file): string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'cctv_' . uniqid() . '.' . strtolower($ext);
        $target = FCPATH . 'shared/cctv/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $fileName;
        }

        throw new \Exception('Gagal memindahkan file yang diunggah.');
    }
}
