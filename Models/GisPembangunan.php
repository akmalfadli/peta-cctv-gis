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

namespace Modules\PetaGIS\Models;

use App\Models\BaseModel;
use App\Traits\ConfigId;

defined('BASEPATH') || exit('No direct script access allowed');

class GisPembangunan extends BaseModel
{
    use ConfigId;

    protected $table = 'gis_pembangunans';

    protected $fillable = [
        'jenis_kegiatan',
        'kategori',
        'sumber_dana',
        'anggaran',
        'lokasi',
        'volume',
        'pelaksana',
        'photo',
        'tahun_anggaran',
        'type',
        'latitude',
        'longitude',
        'coordinates',
        'config_id',
    ];

    protected $casts = [
        'anggaran' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Scope filter pencarian pembangunan.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('jenis_kegiatan', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('sumber_dana', 'like', "%{$search}%")
                  ->orWhere('tahun_anggaran', 'like', "%{$search}%")
                  ->orWhere('pelaksana', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (!empty($filters['tahun_anggaran'])) {
            $query->where('tahun_anggaran', $filters['tahun_anggaran']);
        }

        return $query;
    }
}
