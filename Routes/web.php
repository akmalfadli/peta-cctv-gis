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

// BACKEND - ADMIN CCTV CAMERAS
Route::group('gis_camera', ['namespace' => 'PetaGIS/BackEnd'], static function (): void {
    Route::get('/', 'GisCameraAdminController@index')->name('gis_camera.index');
    Route::match(['get', 'post'], '/datatables', 'GisCameraAdminController@datatables')->name('gis_camera.datatables');
    Route::get('/create', 'GisCameraAdminController@create')->name('gis_camera.create');
    Route::post('/store', 'GisCameraAdminController@store')->name('gis_camera.store');
    Route::get('/edit/{id}', 'GisCameraAdminController@edit')->name('gis_camera.edit');
    Route::post('/update/{id}', 'GisCameraAdminController@update')->name('gis_camera.update');
    Route::get('/delete/{id}', 'GisCameraAdminController@delete')->name('gis_camera.delete');
    
    // Toggle active / public
    Route::post('/toggle_active/{id}', 'GisCameraAdminController@toggleActive')->name('gis_camera.toggle_active');
    Route::post('/toggle_public/{id}', 'GisCameraAdminController@togglePublic')->name('gis_camera.toggle_public');

    // Health Monitor Trigger
    Route::get('/health_check', 'GisCameraAdminController@healthCheck')->name('gis_camera.health_check');

    // Settings configuration
    Route::match(['get', 'post'], '/settings', 'GisCameraAdminController@settings')->name('gis_camera.settings');
});

// BACKEND - ADMIN PEMBANGUNAN DESA (VILLAGE DEVELOPMENT)
Route::group('gis_pembangunan', ['namespace' => 'PetaGIS/BackEnd'], static function (): void {
    Route::get('/', 'GisPembangunanAdminController@index')->name('gis_pembangunan.index');
    Route::match(['get', 'post'], '/datatables', 'GisPembangunanAdminController@datatables')->name('gis_pembangunan.datatables');
    Route::get('/create', 'GisPembangunanAdminController@create')->name('gis_pembangunan.create');
    Route::post('/store', 'GisPembangunanAdminController@store')->name('gis_pembangunan.store');
    Route::get('/edit/{id}', 'GisPembangunanAdminController@edit')->name('gis_pembangunan.edit');
    Route::post('/update/{id}', 'GisPembangunanAdminController@update')->name('gis_pembangunan.update');
    Route::get('/delete/{id}', 'GisPembangunanAdminController@delete')->name('gis_pembangunan.delete');
});

// BACKEND - ADMIN CCTV CATEGORIES
Route::group('gis_category', ['namespace' => 'PetaGIS/BackEnd'], static function (): void {
    Route::get('/', 'GisCategoryAdminController@index')->name('gis_category.index');
    Route::post('/store', 'GisCategoryAdminController@store')->name('gis_category.store');
    Route::post('/update/{id}', 'GisCategoryAdminController@update')->name('gis_category.update');
    Route::get('/delete/{id}', 'GisCategoryAdminController@delete')->name('gis_category.delete');
});

// FRONTEND - PUBLIC VISUALIZATION MAP & APIs
Route::group('petagis', ['namespace' => 'PetaGIS/FrontEnd'], static function (): void {
    Route::get('/', 'GisPublicController@index')->name('petagis.index');
    Route::get('/weather', 'GisPublicController@getWeatherProxy')->name('petagis.weather');
    Route::get('/api_cameras', 'GisPublicController@getCamerasApi')->name('petagis.api_cameras');
    Route::get('/api_cameras/{id}', 'GisPublicController@getCameraDetailApi')->name('petagis.api_camera_detail');
    Route::get('/api_pembangunans', 'GisPublicController@getPembangunansApi')->name('petagis.api_pembangunans');
});
