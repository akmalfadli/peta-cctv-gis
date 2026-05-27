@extends('admin.layouts.index')

@section('title')
    <h1>{{ $title }}</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ site_url('hom_sid') }}"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="{{ site_url('gis_camera') }}">Daftar Kamera CCTV</a></li>
    <li class="active">{{ $camera ? 'Edit Kamera' : 'Tambah Kamera Baru' }}</li>
@endsection

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-ban"></i> Error!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Formulir Informasi Kamera</h3>
                    </div>

                    @php
                        $actionUrl = $camera ? ci_route('gis_camera.update', $camera->id) : ci_route('gis_camera.store');
                    @endphp

                    {!! form_open_multipart($actionUrl, ['id' => 'cctvForm', 'class' => 'form-horizontal']) !!}
                    <div class="box-body">
                        <div class="row">
                            <!-- Left Form Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Nama Kamera <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="name" class="form-control" placeholder="Contoh: Simpang Balai Desa" value="{{ old('name', $camera ? $camera->name : '') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Kategori <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select name="category_id" class="form-control select2" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ old('category_id', $camera ? $camera->category_id : '') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Deskripsi / Lokasi</label>
                                    <div class="col-sm-8">
                                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Terletak di tiang listrik samping gerbang utama masuk balai desa">{{ old('description', $camera ? $camera->description : '') }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Stream Type</label>
                                    <div class="col-sm-8">
                                        <select name="stream_type" id="stream_type" class="form-control select2">
                                            <option value="" {{ old('stream_type', $camera ? $camera->stream_type : '') == '' ? 'selected' : '' }}>-- Tanpa Stream (Hanya Marker Koordinat Tempat) --</option>
                                            <option value="hls" {{ old('stream_type', $camera ? $camera->stream_type : '') == 'hls' ? 'selected' : '' }}>HLS Live Stream (.m3u8)</option>
                                            <option value="youtube" {{ old('stream_type', $camera ? $camera->stream_type : '') == 'youtube' ? 'selected' : '' }}>YouTube Live Embed</option>
                                            <option value="iframe" {{ old('stream_type', $camera ? $camera->stream_type : '') == 'iframe' ? 'selected' : '' }}>IFrame Embed Tag</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">URL / Embed Tag</label>
                                    <div class="col-sm-8">
                                        <textarea name="stream_url" id="stream_url" class="form-control" rows="4" placeholder="Kosongkan jika hanya ingin menambahkan marker lokasi tempat tanpa live feed streaming.">{{ old('stream_url', $camera ? $camera->stream_url : '') }}</textarea>
                                        <span class="help-block" id="url_helper">
                                            Contoh HLS: <code>https://shinobi.desa.id/hls/group/camera/s.m3u8</code><br>
                                            Contoh YouTube: <code>https://www.youtube.com/embed/xxxxxxxx</code>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Cover / Thumbnail</label>
                                    <div class="col-sm-8">
                                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                        @if ($camera && $camera->thumbnail)
                                            <p class="help-block">
                                                <img src="{{ base_url('shared/gis/' . $camera->thumbnail) }}" alt="Thumbnail Saat Ini" style="max-height: 80px; max-width: 150px; margin-top: 5px;" class="img-thumbnail"><br>
                                                <small class="text-info">* Unggah file baru jika ingin mengganti cover thumbnail saat ini.</small>
                                            </p>
                                        @else
                                            <p class="help-block"><small class="text-muted">Gunakan gambar statis sebagai sampul sebelum stream dimuat (opsional).</small></p>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Opsi</label>
                                    <div class="col-sm-8">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $camera ? $camera->is_public : 1) ? 'checked' : '' }}> Akses Publik (Warga)
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $camera ? $camera->is_active : 1) ? 'checked' : '' }}> Aktif Pemantauan
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Map Column (GIS picker) -->
                            <div class="col-md-6" style="border-left: 1px solid #eee;">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Latitude <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="latitude" id="lat" class="form-control" placeholder="Contoh: -7.382046" value="{{ old('latitude', $camera ? $camera->latitude : '') }}" required readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Longitude <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="longitude" id="lng" class="form-control" placeholder="Contoh: 109.364406" value="{{ old('longitude', $camera ? $camera->longitude : '') }}" required readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div id="map_picker" style="height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);"></div>
                                        <p class="help-block text-info" style="margin-top: 5px;"><i class="fa fa-info-circle"></i> Klik pada peta atau geser penanda merah untuk menentukan koordinat lokasi CCTV.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ site_url('gis_camera') }}" class="btn btn-default"><i class="fa fa-undo"></i> Batal</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan Data Kamera</button>
                    </div>
                    {!! form_close() !!}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('css')
    <!-- Leaflet GIS CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .select2-container--default .select2-selection--single {
            height: 34px !important;
            border-radius: 4px !important;
            border: 1px solid #ccc !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- Leaflet GIS JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        $(document).ready(function() {
            // Helper change handler for Stream Type
            $('#stream_type').on('change', function() {
                var val = $(this).val();
                var helper = $('#url_helper');
                var placeholder = '';
                
                if (val === 'hls') {
                    placeholder = 'URL .m3u8, e.g. https://shinobi.desa.id/hls/group/camera/s.m3u8';
                    helper.html('Contoh HLS: <code>https://shinobi.desa.id/hls/group/camera/s.m3u8</code>').show();
                    $('#stream_url').prop('required', true);
                } else if (val === 'youtube') {
                    placeholder = 'YouTube Embed URL, e.g. https://www.youtube.com/embed/jfKfPfyJRdk';
                    helper.html('Contoh YouTube: <code>https://www.youtube.com/embed/jfKfPfyJRdk</code>').show();
                    $('#stream_url').prop('required', true);
                } else if (val === 'iframe') {
                    placeholder = '<iframe src="URL" width="100%" height="300"></iframe>';
                    helper.html('Contoh IFrame: <code>&lt;iframe src="https://stream.desa.id/" width="100%" height="300"&gt;&lt;/iframe&gt;</code>').show();
                    $('#stream_url').prop('required', true);
                } else {
                    placeholder = 'Kosongkan jika hanya ingin menambahkan marker lokasi tempat tanpa live feed streaming.';
                    helper.html('').hide();
                    $('#stream_url').prop('required', false);
                }
                
                $('#stream_url').attr('placeholder', placeholder);
            }).trigger('change');

            // --- LEAFLET COORDINATE PICKER SETUP ---
            // Fallback default coordinates of village center
            var defaultLat = {{ !empty($desa->lat) ? (float)$desa->lat : -7.382046 }};
            var defaultLng = {{ !empty($desa->lng) ? (float)$desa->lng : 109.364406 }};

            var cameraLat = $('#lat').val();
            var cameraLng = $('#lng').val();

            var startLat = cameraLat !== '' ? parseFloat(cameraLat) : defaultLat;
            var startLng = cameraLng !== '' ? parseFloat(cameraLng) : defaultLng;
            var startZoom = cameraLat !== '' ? 16 : 14;

            // Establish base maps
            var layerStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

            var layerSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: '&copy; Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });

            // Initialize Map with default Street View
            var map = L.map('map_picker', {
                layers: [layerStreet]
            }).setView([startLat, startLng], startZoom);

            // Add Layer Control for base map switching (Street vs Satellite)
            var baseMaps = {
                "Peta Jalan": layerStreet,
                "Satelit": layerSatellite
            };

            L.control.layers(baseMaps, null, {
                position: 'topright'
            }).addTo(map);

            // Marker
            var marker = L.marker([startLat, startLng], {
                draggable: true
            }).addTo(map);

            // If coordinates were empty initially (creating new), set current marker center into inputs
            if (cameraLat === '' || cameraLng === '') {
                $('#lat').val(startLat.toFixed(8));
                $('#lng').val(startLng.toFixed(8));
            }

            // Sync marker drag to inputs
            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                $('#lat').val(position.lat.toFixed(8));
                $('#lng').val(position.lng.toFixed(8));
            });

            // Sync map click to marker and inputs
            map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                
                marker.setLatLng([lat, lng]);
                $('#lat').val(lat.toFixed(8));
                $('#lng').val(lng.toFixed(8));
            });

            // Handle Map Resize bug inside bootstrap hidden/tab containers
            setTimeout(function() {
                map.invalidateSize();
            }, 500);
        });
    </script>
@endpush
