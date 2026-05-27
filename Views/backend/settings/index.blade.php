@extends('admin.layouts.index')

@section('title')
    <h1>{{ $title }}</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ site_url('hom_sid') }}"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="{{ site_url('cctv_admin') }}">Daftar Kamera CCTV</a></li>
    <li class="active">Pengaturan Integrasi Cuaca</li>
@endsection

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-check"></i> Sukses!</h4>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Konfigurasi Cuaca OpenWeatherMap</h3>
                        <a href="{{ site_url('cctv_admin') }}" class="btn btn-default btn-xs pull-right"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>
                    </div>

                    {!! form_open(ci_route('cctv_admin.settings'), ['class' => 'form-horizontal']) !!}
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Aktifkan Weather Widget</label>
                            <div class="col-sm-9">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="weather_enabled" value="1" {{ $weather_enabled ? 'checked' : '' }}>
                                        Tampilkan widget info cuaca real-time di pojok kanan atas Peta CCTV Publik.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Koordinat Pusat Desa</label>
                            <div class="col-sm-7">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker text-danger"></i></span>
                                    <input type="text" class="form-control" value="{{ $desa->lat ?? '-' }}, {{ $desa->lng ?? '-' }}" readonly style="background-color: #f9f9f9; cursor: not-allowed; font-family: monospace;">
                                </div>
                                <span class="help-block">
                                    Koordinat di atas disinkronkan secara otomatis dari data <strong>Identitas Desa</strong>. Lokasi ini digunakan sebagai titik acuan prakiraan cuaca OpenWeatherMap untuk wilayah desa Anda.
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">OpenWeatherMap API Key <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="text" name="weather_api_key" class="form-control" placeholder="Masukkan 32 karakter OpenWeatherMap API Key..." value="{{ old('weather_api_key', $weather_api_key) }}">
                                <span class="help-block">
                                    Kunci API diperlukan untuk memanggil layanan data cuaca OpenWeatherMap secara berkala.
                                </span>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 20px;">
                            <div class="col-sm-9 col-sm-offset-3">
                                <div class="callout callout-info" style="margin-bottom: 0;">
                                    <h4><i class="fa fa-info-circle"></i> Panduan Mendapatkan API Key:</h4>
                                    <ol style="padding-left: 20px; margin-top: 5px;">
                                        <li>Kunjungi situs resmi <strong><a href="https://openweathermap.org/" target="_blank" style="color: #fff; text-decoration: underline;">OpenWeatherMap</a></strong>.</li>
                                        <li>Lakukan registrasi akun baru (Akun Free/Gratis sudah sangat cukup untuk mencakup pemanggilan reguler desa).</li>
                                        <li>Setelah masuk, buka menu <strong>"My API Keys"</strong> pada akun profil Anda.</li>
                                        <li>Salin 32 karakter kode API Key yang digenerasi oleh sistem, lalu tempelkan pada kolom di atas.</li>
                                    </ol>
                                    <p style="margin-top: 8px;"><small><em>* Catatan: Setelah didaftarkan pertama kali, OpenWeatherMap memerlukan waktu aktivasi API Key sekitar 10 - 30 menit sebelum kuncinya dapat merespons request data dari peta.</em></small></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="row">
                            <div class="col-sm-9 col-sm-offset-3">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Konfigurasi</button>
                                <a href="{{ site_url('cctv_admin') }}" class="btn btn-default">Batal</a>
                            </div>
                        </div>
                    </div>
                    {!! form_close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
