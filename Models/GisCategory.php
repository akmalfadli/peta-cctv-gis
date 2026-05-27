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
 */

namespace Modules\PetaGIS\Models;

use App\Models\BaseModel;
use App\Traits\ConfigId;

defined('BASEPATH') || exit('No direct script access allowed');

class GisCategory extends BaseModel
{
    use ConfigId;

    protected $table = 'gis_categories';

    protected $fillable = [
        'name',
        'icon',
        'color',
        'config_id',
    ];

    /**
     * Relasi ke daftar kamera di dalam kategori ini.
     */
    public function cameras()
    {
        return $this->hasMany(GisCamera::class, 'category_id');
    }
}
