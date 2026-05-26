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
use Modules\PetaCCTV\Models\CctvCategory;

defined('BASEPATH') || exit('No direct script access allowed');

class CctvCategoryController extends AdminModulController
{
    public $moduleName = 'PetaCCTV';
    public $modul_ini = 'cctv';
    public $sub_modul_ini = 'cctv-category';

    public function __construct()
    {
        parent::__construct();
        isCan('b', $this->modul_ini);
    }

    /**
     * Tampilkan daftar kategori CCTV.
     */
    public function index()
    {
        $categories = CctvCategory::withCount('cameras')->orderBy('name', 'asc')->get();

        return view('cctv::backend.category.index', [
            'categories' => $categories,
            'title' => 'Manajemen Kategori CCTV',
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store()
    {
        isCan('u', $this->modul_ini);

        $name = trim(ci()->input->post('name') ?: '');

        if (empty($name)) {
            set_session('error', 'Nama kategori tidak boleh kosong.');
            return redirect('cctv_category');
        }

        DB::beginTransaction();

        try {
            CctvCategory::create([
                'config_id' => identitas('id'),
                'name' => $name,
            ]);

            DB::commit();
            set_session('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }

        return redirect('cctv_category');
    }

    /**
     * Perbarui kategori yang sudah ada.
     */
    public function update($id)
    {
        isCan('u', $this->modul_ini);

        $category = CctvCategory::findOrFail($id);
        $name = trim(ci()->input->post('name') ?: '');

        if (empty($name)) {
            set_session('error', 'Nama kategori tidak boleh kosong.');
            return redirect('cctv_category');
        }

        DB::beginTransaction();

        try {
            $category->update([
                'name' => $name,
            ]);

            DB::commit();
            set_session('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            set_session('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }

        return redirect('cctv_category');
    }

    /**
     * Hapus kategori.
     */
    public function delete($id)
    {
        isCan('h', $this->modul_ini);

        $category = CctvCategory::findOrFail($id);

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

        return redirect('cctv_category');
    }
}
