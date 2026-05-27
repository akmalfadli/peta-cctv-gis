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
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('gis_categories')) {
            Schema::table('gis_categories', static function (Blueprint $table) {
                if (!Schema::hasColumn('gis_categories', 'icon')) {
                    $table->string('icon', 100)->default('fa-video');
                }
                if (!Schema::hasColumn('gis_categories', 'color')) {
                    $table->string('color', 50)->default('#10b981');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gis_categories')) {
            Schema::table('gis_categories', static function (Blueprint $table) {
                if (Schema::hasColumn('gis_categories', 'icon')) {
                    $table->dropColumn('icon');
                }
                if (Schema::hasColumn('gis_categories', 'color')) {
                    $table->dropColumn('color');
                }
            });
        }
    }
};
