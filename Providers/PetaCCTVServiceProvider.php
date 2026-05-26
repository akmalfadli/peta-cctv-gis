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

namespace Modules\PetaCCTV\Providers;

use Illuminate\Support\ServiceProvider;

class PetaCCTVServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $moduleName = 'PetaCCTV';

    /**
     * @var string
     */
    protected $moduleNameLower = 'cctv';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViews();
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
