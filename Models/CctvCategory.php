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

namespace Modules\PetaCCTV\Models;

use App\Models\BaseModel;
use App\Traits\ConfigId;

defined('BASEPATH') || exit('No direct script access allowed');

class CctvCategory extends BaseModel
{
    use ConfigId;

    protected $table = 'cctv_categories';

    protected $fillable = [
        'name',
        'config_id',
    ];

    /**
     * Relasi ke daftar kamera di dalam kategori ini.
     */
    public function cameras()
    {
        return $this->hasMany(CctvCamera::class, 'category_id');
    }
}
