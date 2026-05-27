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
use Modules\PetaGIS\Models\GisPembangunan;

defined('BASEPATH') || exit('No direct script access allowed');

class GisPembangunanAdminController extends AdminModulController
{
    public $moduleName = 'PetaGIS';
    public $modul_ini = 'gis';
    public $sub_modul_ini = 'gis-pembangunan';

    public function __construct()
    {
        parent::__construct();
        isCan('b', $this->modul_ini);
        $this->ensureUploadDirectory();
    }

    /**
     * Tampilkan daftar pembangunan desa.
     */
    public function index()
    {
        $stats = [
            'total' => GisPembangunan::count(),
            'jalan' => GisPembangunan::where('type', 'road')->count(),
            'gedung' => GisPembangunan::where('type', 'building')->count(),
            'total_anggaran' => GisPembangunan::sum('anggaran'),
        ];

        return view('gis::backend.pembangunan.index', [
            'stats' => $stats,
            'title' => 'Manajemen Pembangunan Desa',
        ]);
    }

    /**
     * DataTables server-side untuk pembangunan.
     */
    public function datatables()
    {
        $filters = [
            'search' => ci()->input->get_post('search'),
            'type' => ci()->input->get_post('type'),
            'kategori' => ci()->input->get_post('kategori'),
        ];

        $query = GisPembangunan::filter($filters);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('photo_view', function ($row) {
                if ($row->photo) {
                    return '<img src="' . base_url('shared/gis/' . $row->photo) . '" alt="Foto" class="img-thumbnail" style="max-height: 50px; max-width: 80px;">';
                }
                return '<span class="label label-default">No Photo</span>';
            })
            ->editColumn('kategori', function ($row) {
                switch ($row->kategori) {
                    case 'pendidikan':
                        return '<span class="label" style="background-color: #6366f1; color: #fff; font-weight: bold; padding: .2em .6em .3em;"><i class="fa fa-graduation-cap"></i> Pendidikan</span>';
                    case 'kesehatan':
                        return '<span class="label" style="background-color: #ef4444; color: #fff; font-weight: bold; padding: .2em .6em .3em;"><i class="fa fa-heartbeat"></i> Kesehatan</span>';
                    case 'ekonomi':
                        return '<span class="label" style="background-color: #10b981; color: #fff; font-weight: bold; padding: .2em .6em .3em;"><i class="fa fa-money"></i> Ekonomi</span>';
                    case 'lingkungan':
                        return '<span class="label" style="background-color: #f59e0b; color: #fff; font-weight: bold; padding: .2em .6em .3em;"><i class="fa fa-leaf"></i> Lingkungan</span>';
                    case 'infrastruktur':
                    default:
                        return '<span class="label" style="background-color: #3b82f6; color: #fff; font-weight: bold; padding: .2em .6em .3em;"><i class="fa fa-road"></i> Infrastruktur</span>';
                }
            })
            ->editColumn('type', function ($row) {
                if ($row->type === 'road') {
                    return '<span class="label label-info"><i class="fa fa-road"></i> Jalan</span>';
                }
                return '<span class="label label-warning"><i class="fa fa-building"></i> Gedung</span>';
            })
            ->editColumn('anggaran', function ($row) {
                return 'Rp ' . number_format($row->anggaran, 0, ',', '.');
            })
            ->addColumn('aksi', function ($row) {
                $html = '<a href="' . ci_route('gis_pembangunan.edit', $row->id) . '" class="btn btn-primary btn-xs" title="Edit Proyek"><i class="fa fa-pencil"></i></a> ';
                $html .= '<a href="' . ci_route('gis_pembangunan.delete', $row->id) . '" class="btn btn-danger btn-xs btn-delete-pembangunan" title="Hapus"><i class="fa fa-trash"></i></a>';
                return $html;
            })
            ->rawColumns(['photo_view', 'kategori', 'type', 'aksi'])
            ->make();
    }

    /**
     * Halaman tambah pembangunan.
     */
    public function create()
    {
        isCan('u', $this->modul_ini);

        $desa = identitas();

        return view('gis::backend.pembangunan.form', [
            'pembangunan' => null,
            'desa' => $desa,
            'title' => 'Tambah Proyek Pembangunan Baru',
        ]);
    }

    /**
     * Simpan pembangunan baru.
     */
    public function store()
    {
        isCan('u', $this->modul_ini);

        $jenis_kegiatan = trim(ci()->input->post('jenis_kegiatan') ?: '');
        $kategori = ci()->input->post('kategori') ?: 'infrastruktur';
        $tahun_anggaran = trim(ci()->input->post('tahun_anggaran') ?: '');
        $sumber_dana = trim(ci()->input->post('sumber_dana') ?: '');
        $anggaran = ci()->input->post('anggaran') ?: 0;
        $lokasi = trim(ci()->input->post('lokasi') ?: '');
        $volume = trim(ci()->input->post('volume') ?: '');
        $pelaksana = trim(ci()->input->post('pelaksana') ?: '');
        $type = ci()->input->post('type') ?: 'building';
        $latitude = ci()->input->post('latitude') ?: null;
        $longitude = ci()->input->post('longitude') ?: null;
        $coordinates = ci()->input->post('coordinates') ?: null;

        // Validations
        if (empty($jenis_kegiatan)) {
            set_session('error', 'Jenis Kegiatan tidak boleh kosong.');
            return redirect('gis_pembangunan/create');
        }

        if (empty($tahun_anggaran)) {
            set_session('error', 'Tahun Anggaran tidak boleh kosong.');
            return redirect('gis_pembangunan/create');
        }

        $allowedKategori = ['infrastruktur', 'pendidikan', 'kesehatan', 'ekonomi', 'lingkungan'];
        if (!in_array($kategori, $allowedKategori)) {
            set_session('error', 'Kategori Pembangunan tidak valid.');
            return redirect('gis_pembangunan/create');
        }

        if ($type === 'building' && (empty($latitude) || empty($longitude))) {
            set_session('error', 'Lokasi titik koordinat gedung/bangunan tidak boleh kosong.');
            return redirect('gis_pembangunan/create');
        }

        if ($type === 'road' && empty($coordinates)) {
            set_session('error', 'Garis jalan harus digambar pada peta.');
            return redirect('gis_pembangunan/create');
        }

        // Auto-extract starting coordinates for roads
        if ($type === 'road' && !empty($coordinates)) {
            $parsed = json_decode($coordinates, true);
            if (is_array($parsed) && count($parsed) > 0) {
                $latitude = $parsed[0][0];
                $longitude = $parsed[0][1];
            }
        }

        DB::beginTransaction();

        try {
            // Handle Photo Upload
            $photo = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = $this->handleUpload($_FILES['photo']);
            }

            GisPembangunan::create([
                'config_id' => identitas('id'),
                'jenis_kegiatan' => $jenis_kegiatan,
                'kategori' => $kategori,
                'tahun_anggaran' => $tahun_anggaran,
                'sumber_dana' => $sumber_dana,
                'anggaran' => $anggaran,
                'lokasi' => $lokasi,
                'volume' => $volume,
                'pelaksana' => $pelaksana,
                'photo' => $photo,
                'type' => $type,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'coordinates' => $coordinates,
            ]);

            DB::commit();
            set_session('success', 'Proyek "' . $jenis_kegiatan . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menyimpan pembangunan: ' . $e->getMessage());
            return redirect('gis_pembangunan/create');
        }

        return redirect('gis_pembangunan');
    }

    /**
     * Halaman edit pembangunan.
     */
    public function edit($id)
    {
        isCan('u', $this->modul_ini);

        $pembangunan = GisPembangunan::findOrFail($id);
        $desa = identitas();

        return view('gis::backend.pembangunan.form', [
            'pembangunan' => $pembangunan,
            'desa' => $desa,
            'title' => 'Edit Proyek: ' . $pembangunan->jenis_kegiatan,
        ]);
    }

    /**
     * Perbarui pembangunan.
     */
    public function update($id)
    {
        isCan('u', $this->modul_ini);

        $pembangunan = GisPembangunan::findOrFail($id);

        $jenis_kegiatan = trim(ci()->input->post('jenis_kegiatan') ?: '');
        $kategori = ci()->input->post('kategori') ?: 'infrastruktur';
        $tahun_anggaran = trim(ci()->input->post('tahun_anggaran') ?: '');
        $sumber_dana = trim(ci()->input->post('sumber_dana') ?: '');
        $anggaran = ci()->input->post('anggaran') ?: 0;
        $lokasi = trim(ci()->input->post('lokasi') ?: '');
        $volume = trim(ci()->input->post('volume') ?: '');
        $pelaksana = trim(ci()->input->post('pelaksana') ?: '');
        $type = ci()->input->post('type') ?: 'building';
        $latitude = ci()->input->post('latitude') ?: null;
        $longitude = ci()->input->post('longitude') ?: null;
        $coordinates = ci()->input->post('coordinates') ?: null;

        // Validations
        if (empty($jenis_kegiatan)) {
            set_session('error', 'Jenis Kegiatan tidak boleh kosong.');
            return redirect('gis_pembangunan/edit/' . $id);
        }

        if (empty($tahun_anggaran)) {
            set_session('error', 'Tahun Anggaran tidak boleh kosong.');
            return redirect('gis_pembangunan/edit/' . $id);
        }

        $allowedKategori = ['infrastruktur', 'pendidikan', 'kesehatan', 'ekonomi', 'lingkungan'];
        if (!in_array($kategori, $allowedKategori)) {
            set_session('error', 'Kategori Pembangunan tidak valid.');
            return redirect('gis_pembangunan/edit/' . $id);
        }

        if ($type === 'building' && (empty($latitude) || empty($longitude))) {
            set_session('error', 'Lokasi titik koordinat gedung/bangunan tidak boleh kosong.');
            return redirect('gis_pembangunan/edit/' . $id);
        }

        if ($type === 'road' && empty($coordinates)) {
            set_session('error', 'Garis jalan harus digambar pada peta.');
            return redirect('gis_pembangunan/edit/' . $id);
        }

        // Auto-extract starting coordinates for roads
        if ($type === 'road' && !empty($coordinates)) {
            $parsed = json_decode($coordinates, true);
            if (is_array($parsed) && count($parsed) > 0) {
                $latitude = $parsed[0][0];
                $longitude = $parsed[0][1];
            }
        }

        DB::beginTransaction();

        try {
            $photo = $pembangunan->photo;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Delete old photo if exists
                if ($photo && file_exists(FCPATH . 'shared/gis/' . $photo)) {
                    @unlink(FCPATH . 'shared/gis/' . $photo);
                }
                $photo = $this->handleUpload($_FILES['photo']);
            }

            $pembangunan->update([
                'jenis_kegiatan' => $jenis_kegiatan,
                'kategori' => $kategori,
                'tahun_anggaran' => $tahun_anggaran,
                'sumber_dana' => $sumber_dana,
                'anggaran' => $anggaran,
                'lokasi' => $lokasi,
                'volume' => $volume,
                'pelaksana' => $pelaksana,
                'photo' => $photo,
                'type' => $type,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'coordinates' => $coordinates,
            ]);

            DB::commit();
            set_session('success', 'Proyek "' . $jenis_kegiatan . '" berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal memperbarui proyek: ' . $e->getMessage());
            return redirect('gis_pembangunan/edit/' . $id);
        }

        return redirect('gis_pembangunan');
    }

    /**
     * Hapus pembangunan.
     */
    public function delete($id)
    {
        isCan('h', $this->modul_ini);

        $pembangunan = GisPembangunan::findOrFail($id);

        DB::beginTransaction();

        try {
            $jenis_kegiatan = $pembangunan->jenis_kegiatan;

            // Delete photo file
            if ($pembangunan->photo && file_exists(FCPATH . 'shared/gis/' . $pembangunan->photo)) {
                @unlink(FCPATH . 'shared/gis/' . $pembangunan->photo);
            }

            $pembangunan->delete();

            DB::commit();
            set_session('success', 'Proyek "' . $jenis_kegiatan . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menghapus proyek: ' . $e->getMessage());
        }

        return redirect('gis_pembangunan');
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
        $fileName = 'pembangunan_' . uniqid() . '.' . strtolower($ext);
        $target = FCPATH . 'shared/gis/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $fileName;
        }

        throw new \Exception('Gagal memindahkan file.');
    }
}
