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
use Modules\PetaGIS\Models\GisCategory;

defined('BASEPATH') || exit('No direct script access allowed');

class GisCategoryAdminController extends AdminModulController
{
    public $moduleName = 'PetaGIS';
    public $modul_ini = 'gis';
    public $sub_modul_ini = 'gis-category';

    public function __construct()
    {
        parent::__construct();
        isCan('b', $this->modul_ini);
    }

    /**
     * Tampilkan daftar kategori GIS.
     */
    public function index()
    {
        $categories = GisCategory::withCount('cameras')->orderBy('name', 'asc')->get();

        return view('gis::backend.category.index', [
            'categories' => $categories,
            'title' => 'Manajemen Kategori GIS',
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store()
    {
        isCan('u', $this->modul_ini);

        $name = trim(ci()->input->post('name') ?: '');
        $icon = trim(ci()->input->post('icon') ?: 'fa-video');
        $color = trim(ci()->input->post('color') ?: '#10b981');

        if (empty($name)) {
            set_session('error', 'Nama kategori tidak boleh kosong.');
            return redirect('gis_category');
        }

        DB::beginTransaction();

        try {
            GisCategory::create([
                'config_id' => identitas('id'),
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
            ]);

            DB::commit();
            set_session('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }

        return redirect('gis_category');
    }

    /**
     * Perbarui kategori yang sudah ada.
     */
    public function update($id)
    {
        isCan('u', $this->modul_ini);

        $category = GisCategory::findOrFail($id);
        $name = trim(ci()->input->post('name') ?: '');
        $icon = trim(ci()->input->post('icon') ?: 'fa-video');
        $color = trim(ci()->input->post('color') ?: '#10b981');

        if (empty($name)) {
            set_session('error', 'Nama kategori tidak boleh kosong.');
            return redirect('gis_category');
        }

        DB::beginTransaction();

        try {
            $category->update([
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
            ]);

            DB::commit();
            set_session('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }

        return redirect('gis_category');
    }

    /**
     * Hapus kategori.
     */
    public function delete($id)
    {
        isCan('h', $this->modul_ini);

        $category = GisCategory::findOrFail($id);

        DB::beginTransaction();

        try {
            $name = $category->name;
            $category->delete();

            DB::commit();
            set_session('success', 'Kategori "' . $name . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }

        return redirect('gis_category');
    }
}
