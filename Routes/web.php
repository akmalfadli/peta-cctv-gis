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

// BACKEND - ADMIN CCTV CAMERAS
Route::group('cctv_admin', ['namespace' => 'PetaCCTV/BackEnd'], static function (): void {
    Route::get('/', 'CctvAdminController@index')->name('cctv_admin.index');
    Route::match(['get', 'post'], '/datatables', 'CctvAdminController@datatables')->name('cctv_admin.datatables');
    Route::get('/create', 'CctvAdminController@create')->name('cctv_admin.create');
    Route::post('/store', 'CctvAdminController@store')->name('cctv_admin.store');
    Route::get('/edit/{id}', 'CctvAdminController@edit')->name('cctv_admin.edit');
    Route::post('/update/{id}', 'CctvAdminController@update')->name('cctv_admin.update');
    Route::get('/delete/{id}', 'CctvAdminController@delete')->name('cctv_admin.delete');
    
    // Toggle active / public
    Route::post('/toggle_active/{id}', 'CctvAdminController@toggleActive')->name('cctv_admin.toggle_active');
    Route::post('/toggle_public/{id}', 'CctvAdminController@togglePublic')->name('cctv_admin.toggle_public');

    // Health Monitor Trigger
    Route::get('/health_check', 'CctvAdminController@healthCheck')->name('cctv_admin.health_check');

    // Settings configuration
    Route::match(['get', 'post'], '/settings', 'CctvAdminController@settings')->name('cctv_admin.settings');
});

// BACKEND - ADMIN CCTV CATEGORIES
Route::group('cctv_category', ['namespace' => 'PetaCCTV/BackEnd'], static function (): void {
    Route::get('/', 'CctvCategoryController@index')->name('cctv_category.index');
    Route::post('/store', 'CctvCategoryController@store')->name('cctv_category.store');
    Route::post('/update/{id}', 'CctvCategoryController@update')->name('cctv_category.update');
    Route::get('/delete/{id}', 'CctvCategoryController@delete')->name('cctv_category.delete');
});

// FRONTEND - PUBLIC VISUALIZATION MAP & APIs
Route::group('cctv', ['namespace' => 'PetaCCTV/FrontEnd'], static function (): void {
    Route::get('/', 'CctvPublicController@index')->name('cctv.index');
    Route::get('/api_cameras', 'CctvPublicController@getCamerasApi')->name('cctv.api_cameras');
    Route::get('/api_cameras/{id}', 'CctvPublicController@getCameraDetailApi')->name('cctv.api_camera_detail');
});
