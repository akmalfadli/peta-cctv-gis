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
        if (Schema::hasTable('cctv_cameras')) {
            Schema::rename('cctv_cameras', 'gis_cameras');
        } elseif (!Schema::hasTable('gis_cameras')) {
            Schema::create('gis_cameras', static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->configId();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->text('stream_url');
                $table->string('stream_type', 50)->comment('hls, youtube, iframe');
                $table->string('thumbnail', 255)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->tinyInteger('is_public')->default(1);
                $table->tinyInteger('is_active')->default(1);
                $table->enum('status', ['online', 'offline'])->default('online');
                $table->dateTime('last_online_at')->nullable();
                $table->timestamps();

                $table->index('category_id', 'gis_cameras_category_fk');
                $table->foreign('category_id', 'gis_cameras_category_fk')
                    ->references('id')->on('gis_categories')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_cameras');
    }
};
