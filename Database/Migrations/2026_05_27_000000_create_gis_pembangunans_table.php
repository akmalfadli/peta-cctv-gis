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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('gis_pembangunans')) {
            Schema::create('gis_pembangunans', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->configId();
                $table->string('jenis_kegiatan', 255);
                $table->string('kategori', 50)->default('infrastruktur');
                $table->string('tahun_anggaran', 20)->nullable();
                $table->string('sumber_dana', 255)->nullable();
                $table->decimal('anggaran', 15, 2)->nullable();
                $table->string('lokasi', 255)->nullable();
                $table->string('volume', 255)->nullable();
                $table->string('pelaksana', 255)->nullable();
                $table->string('photo', 255)->nullable();
                $table->enum('type', ['road', 'building'])->default('building');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->text('coordinates')->nullable(); // JSON coordinates for roads: [[lat, lng], [lat, lng], ...]
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_pembangunans');
    }
};
