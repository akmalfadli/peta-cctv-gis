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

use Modules\PetaCCTV\Models\CctvCamera;
use Modules\PetaCCTV\Models\CctvCategory;

defined('BASEPATH') || exit('No direct script access allowed');

class CctvPublicController extends WebModulController
{
    public $moduleName = 'PetaCCTV';
    public $modul_ini = 'cctv';

    public function __construct()
    {
        // Don't enforce admin login check for the public controller
        // but inherit OpenSID standard libraries if needed.
        parent::__construct();
    }

    /**
     * Tampilkan halaman peta publik CCTV.
     */
    public function index()
    {
        $categories = CctvCategory::orderBy('name', 'asc')->get();
        $desa = identitas();

        return view('cctv::frontend.map', [
            'categories' => $categories,
            'desa' => $desa,
            'title' => 'Peta Pemantauan CCTV ' . (identitas('nama_desa') ? ucwords(setting('sebutan_desa')) . ' ' . ucwords(identitas('nama_desa')) : 'Desa'),
        ]);
    }

    /**
     * API: Dapatkan data semua kamera yang aktif dan publik.
     */
    public function getCamerasApi()
    {
        $filters = [
            'search' => ci()->input->get('search'),
            'category_id' => ci()->input->get('category_id'),
            'status' => ci()->input->get('status'),
        ];

        $cameras = CctvCamera::with('category')
            ->active()
            ->public()
            ->filter($filters)
            ->get()
            ->map(function ($cam) {
                // Ensure thumbnail absolute URL
                $thumbUrl = null;
                if ($cam->thumbnail) {
                    $thumbUrl = base_url('shared/cctv/' . $cam->thumbnail);
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
        $cam = CctvCamera::with('category')
            ->active()
            ->public()
            ->find($id);

        if (!$cam) {
            return json(['error' => 'Kamera tidak ditemukan atau berstatus privat.'], 404);
        }

        $thumbUrl = null;
        if ($cam->thumbnail) {
            $thumbUrl = base_url('shared/cctv/' . $cam->thumbnail);
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
}
