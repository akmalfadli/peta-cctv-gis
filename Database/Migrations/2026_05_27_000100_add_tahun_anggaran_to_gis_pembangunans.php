<?php

/*
 * File ini bagian dari:
 *
 * Modul Peta GIS untuk OpenSID
 *
 * @package   Modul Peta GIS untuk OpenSID
 * @author    Akmal Fadli
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('gis_pembangunans') && ! Schema::hasColumn('gis_pembangunans', 'tahun_anggaran')) {
            Schema::table('gis_pembangunans', function (Blueprint $table) {
                $table->string('tahun_anggaran', 20)->nullable()->after('kategori');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gis_pembangunans') && Schema::hasColumn('gis_pembangunans', 'tahun_anggaran')) {
            Schema::table('gis_pembangunans', function (Blueprint $table) {
                $table->dropColumn('tahun_anggaran');
            });
        }
    }
};
