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

namespace Modules\PetaGIS\Providers;

use Illuminate\Support\ServiceProvider;

class PetaGISServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'PetaGIS';

    /**
     * @var string
     */
    protected $moduleNameLower = 'gis';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViews();
        $this->publishAssets();
    }

    /**
     * Publish module assets automatically to the public assets directory on boot.
     */
    protected function publishAssets(): void
    {
        $moduleAssetSource = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Assets';
        $publicAssetDest = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'gis';

        if (file_exists($moduleAssetSource)) {
            $this->copyDirectory($moduleAssetSource, $publicAssetDest);
        }
    }

    /**
     * Recursively copy a directory's contents.
     */
    protected function copyDirectory(string $src, string $dst): void
    {
        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }

        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    $this->copyDirectory($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    $srcFile = $src . DIRECTORY_SEPARATOR . $file;
                    $dstFile = $dst . DIRECTORY_SEPARATOR . $file;
                    if (!file_exists($dstFile) || filemtime($srcFile) > filemtime($dstFile)) {
                        $parentDir = dirname($dstFile);
                        if (!file_exists($parentDir)) {
                            mkdir($parentDir, 0755, true);
                        }
                        copy($srcFile, $dstFile);
                    }
                }
            }
        }
        closedir($dir);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $sourcePath = FCPATH . 'Modules' . DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR . 'Views';

        if (file_exists($sourcePath)) {
            $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
        }
    }
}
