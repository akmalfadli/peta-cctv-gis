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

class GisCamera extends BaseModel
{
    use ConfigId;

    protected $table = 'gis_cameras';

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'stream_url',
        'stream_type',
        'thumbnail',
        'category_id',
        'is_public',
        'is_active',
        'status',
        'last_online_at',
        'config_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'category_id' => 'integer',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'last_online_at' => 'datetime',
    ];

    /**
     * Relasi ke Kategori CCTV.
     */
    public function category()
    {
        return $this->belongsTo(GisCategory::class, 'category_id');
    }

    /**
     * Scope filter untuk data aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope filter untuk data publik (bisa diakses warga).
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', 1);
    }

    /**
     * Scope filter pencarian untuk DataTables atau pencarian publik.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_public']) && $filters['is_public'] !== '') {
            $query->where('is_public', $filters['is_public']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        return $query;
    }
}
