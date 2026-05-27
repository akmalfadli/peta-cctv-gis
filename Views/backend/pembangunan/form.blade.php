@extends('admin.layouts.index')

@section('title')
    <h1>{{ $title }}</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ site_url('hom_sid') }}"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="{{ site_url('gis_pembangunan') }}">Pembangunan Desa</a></li>
    <li class="active">{{ $pembangunan ? 'Edit Proyek' : 'Tambah Proyek Baru' }}</li>
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
                        <h3 class="box-title">Formulir Detail & Pemetaan Proyek</h3>
                    </div>

                    @php
                        $actionUrl = $pembangunan ? ci_route('gis_pembangunan.update', $pembangunan->id) : ci_route('gis_pembangunan.store');
                    @endphp

                    {!! form_open_multipart($actionUrl, ['id' => 'pembangunanForm', 'class' => 'form-horizontal']) !!}
                    
                    <!-- Hidden field to store road polyline coordinates -->
                    <input type="hidden" name="coordinates" id="coordinates" value="{{ old('coordinates', $pembangunan ? $pembangunan->coordinates : '') }}">

                    <div class="box-body">
                        <div class="row">
                            <!-- Left Column: Form Details -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="jenis_kegiatan" class="form-control" placeholder="Contoh: Pengaspalan Jalan Dusun II" value="{{ old('jenis_kegiatan', $pembangunan ? $pembangunan->jenis_kegiatan : '') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Kategori Pembangunan <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select name="kategori" id="project_kategori" class="form-control select2" required>
                                            <option value="infrastruktur" {{ old('kategori', $pembangunan ? $pembangunan->kategori : '') === 'infrastruktur' ? 'selected' : '' }}>Pembangunan Infrastruktur Desa</option>
                                            <option value="pendidikan" {{ old('kategori', $pembangunan ? $pembangunan->kategori : '') === 'pendidikan' ? 'selected' : '' }}>Pembangunan Bidang Pendidikan</option>
                                            <option value="kesehatan" {{ old('kategori', $pembangunan ? $pembangunan->kategori : '') === 'kesehatan' ? 'selected' : '' }}>Pembangunan Bidang Kesehatan</option>
                                            <option value="ekonomi" {{ old('kategori', $pembangunan ? $pembangunan->kategori : '') === 'ekonomi' ? 'selected' : '' }}>Pembangunan Ekonomi Desa</option>
                                            <option value="lingkungan" {{ old('kategori', $pembangunan ? $pembangunan->kategori : '') === 'lingkungan' ? 'selected' : '' }}>Pembangunan Lingkungan & Kebencanaan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Tahun Anggaran <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="number" name="tahun_anggaran" class="form-control" placeholder="Contoh: 2026" min="2000" max="2099" value="{{ old('tahun_anggaran', $pembangunan ? $pembangunan->tahun_anggaran : date('Y')) }}" required>
                                        <p class="help-block"><small class="text-muted">Tahun anggaran pelaksanaan kegiatan.</small></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Tipe Pembangunan <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select name="type" id="project_type" class="form-control select2" required>
                                            <option value="building" {{ old('type', $pembangunan ? $pembangunan->type : '') === 'building' ? 'selected' : '' }}>Gedung / Bangunan (Titik)</option>
                                            <option value="road" {{ old('type', $pembangunan ? $pembangunan->type : '') === 'road' ? 'selected' : '' }}>Jalan / Jembatan (Garis)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Sumber Dana</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="sumber_dana" class="form-control" placeholder="Contoh: Dana Desa (DD) / ADD" value="{{ old('sumber_dana', $pembangunan ? $pembangunan->sumber_dana : '') }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Anggaran (Rp)</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="anggaran" class="form-control" placeholder="Contoh: 150000000" value="{{ old('anggaran', $pembangunan ? $pembangunan->anggaran : '') }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Lokasi Kegiatan</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: RT 02 / RW 04 Dusun II" value="{{ old('lokasi', $pembangunan ? $pembangunan->lokasi : '') }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Volume / Dimensi</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="volume" class="form-control" placeholder="Contoh: P 500m x L 3m" value="{{ old('volume', $pembangunan ? $pembangunan->volume : '') }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Pelaksana Proyek</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="pelaksana" class="form-control" placeholder="Contoh: Tim Pelaksana Kegiatan (TPK)" value="{{ old('pelaksana', $pembangunan ? $pembangunan->pelaksana : '') }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Foto Dokumentasi</label>
                                    <div class="col-sm-8">
                                        <input type="file" name="photo" class="form-control" accept="image/*">
                                        @if ($pembangunan && $pembangunan->photo)
                                            <p class="help-block">
                                                <img src="{{ base_url('shared/gis/' . $pembangunan->photo) }}" alt="Foto Dokumentasi" style="max-height: 100px; max-width: 180px; margin-top: 5px;" class="img-thumbnail"><br>
                                                <small class="text-info">* Unggah foto baru jika ingin mengganti dokumentasi saat ini.</small>
                                            </p>
                                        @else
                                            <p class="help-block"><small class="text-muted">Format file gambar jpg, jpeg, png (opsional).</small></p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Leaflet GIS Interactive Canvas -->
                            <div class="col-md-6" style="border-left: 1px solid #eee;">
                                <!-- Single Marker Lat/Lng Coordinates -->
                                <div id="coord_inputs">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Latitude <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" name="latitude" id="lat" class="form-control" placeholder="-7.382046" value="{{ old('latitude', $pembangunan ? $pembangunan->latitude : '') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Longitude <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" name="longitude" id="lng" class="form-control" placeholder="109.364406" value="{{ old('longitude', $pembangunan ? $pembangunan->longitude : '') }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Polyline Drawing Canvas Tools -->
                                <div id="drawing_tools" style="display: none; background: #f4f6f9; border: 1px dashed #ccc; border-radius: 4px; padding: 10px; margin-bottom: 15px;">
                                    <span class="text-info" style="font-weight: 600;"><i class="fa fa-pencil"></i> Alat Menggambar Garis Jalan:</span>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-sm-6">
                                            <button type="button" id="btn_undo_point" class="btn btn-warning btn-xs btn-block"><i class="fa fa-undo"></i> Batal Titik Terakhir</button>
                                        </div>
                                        <div class="col-sm-6">
                                            <button type="button" id="btn_clear_line" class="btn btn-danger btn-xs btn-block"><i class="fa fa-trash"></i> Hapus Semua Titik</button>
                                        </div>
                                    </div>
                                    <div style="margin-top: 5px; font-size: 11px;" class="text-muted">
                                        * Klik pada peta secara berurutan untuk menggambar garis jalan. Setiap klik akan menambahkan titik penanda baru yang terhubung.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div id="map_picker" style="height: 380px; width: 100%; border: 1px solid #ccc; border-radius: 4px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);"></div>
                                        <p class="help-block text-primary" id="map_instruction" style="margin-top: 5px; font-weight: 600;">
                                            <i class="fa fa-info-circle"></i> Klik pada peta untuk menempatkan titik penanda proyek.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ site_url('gis_pembangunan') }}" class="btn btn-default"><i class="fa fa-undo"></i> Batal</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan Data Proyek</button>
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
            var defaultLat = {{ !empty($desa->lat) ? (float)$desa->lat : -7.382046 }};
            var defaultLng = {{ !empty($desa->lng) ? (float)$desa->lng : 109.364406 }};

            var buildingLat = $('#lat').val();
            var buildingLng = $('#lng').val();
            var roadPointsStr = $('#coordinates').val();

            var startLat = defaultLat;
            var startLng = defaultLng;
            var startZoom = 14;

            // Extract map center
            if (buildingLat !== '' && buildingLng !== '') {
                startLat = parseFloat(buildingLat);
                startLng = parseFloat(buildingLng);
                startZoom = 16;
            } else if (roadPointsStr !== '') {
                try {
                    var parsed = JSON.parse(roadPointsStr);
                    if (parsed.length > 0) {
                        startLat = parsed[0][0];
                        startLng = parsed[0][1];
                        startZoom = 16;
                    }
                } catch(e) {}
            }

            // Initialize Map
            var map = L.map('map_picker').setView([startLat, startLng], startZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Layer holders
            var marker = null;
            var polyline = null;
            var pointMarkers = []; // visually show dots on polyline vertices for easy tracking
            var polylineLatLngs = [];

            // Helper to render current polyline layer and vertices
            function renderPolyline() {
                // Clear existing
                if (polyline) {
                    map.removeLayer(polyline);
                }
                pointMarkers.forEach(function(m) {
                    map.removeLayer(m);
                });
                pointMarkers = [];

                if (polylineLatLngs.length === 0) {
                    $('#coordinates').val('');
                    return;
                }

                // Draw line
                polyline = L.polyline(polylineLatLngs, {
                    color: '#00c0ef',
                    weight: 5,
                    opacity: 0.8
                }).addTo(map);

                // Add vertices markers
                polylineLatLngs.forEach(function(latlng, index) {
                    var vert = L.circleMarker(latlng, {
                        radius: 6,
                        fillColor: '#fff',
                        color: '#00a65a',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(map);
                    
                    vert.bindTooltip("Titik " + (index + 1), { permanent: false, direction: 'top' });
                    pointMarkers.push(vert);
                });

                // Update hidden coordinates json
                $('#coordinates').val(JSON.stringify(polylineLatLngs));
            }

            // Load Existing Data
            if (roadPointsStr !== '') {
                try {
                    var parsed = JSON.parse(roadPointsStr);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        polylineLatLngs = parsed;
                        renderPolyline();
                    }
                } catch(e) {}
            }

            if (buildingLat !== '' && buildingLng !== '') {
                marker = L.marker([parseFloat(buildingLat), parseFloat(buildingLng)], {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function() {
                    var pos = marker.getLatLng();
                    $('#lat').val(pos.lat.toFixed(8));
                    $('#lng').val(pos.lng.toFixed(8));
                });
            }

            // Type Switching Handler
            function updateMapLayout(type) {
                if (type === 'road') {
                    // Show drawing canvas
                    $('#drawing_tools').show();
                    $('#coord_inputs').hide();
                    $('#map_instruction').html('<i class="fa fa-info-circle"></i> Klik berurutan pada peta untuk membuat rute jalan.');

                    // Clear single marker
                    if (marker) {
                        map.removeLayer(marker);
                        marker = null;
                    }
                    $('#lat').val('');
                    $('#lng').val('');

                    // Render polyline
                    renderPolyline();
                } else {
                    // Show single marker coordinat
                    $('#drawing_tools').hide();
                    $('#coord_inputs').show();
                    $('#map_instruction').html('<i class="fa fa-info-circle"></i> Klik pada peta atau geser penanda merah untuk menentukan lokasi gedung.');

                    // Clear polyline
                    if (polyline) {
                        map.removeLayer(polyline);
                        polyline = null;
                    }
                    pointMarkers.forEach(function(m) {
                        map.removeLayer(m);
                    });
                    pointMarkers = [];
                    polylineLatLngs = [];
                    $('#coordinates').val('');

                    // Add building marker if none exists
                    var curLat = parseFloat($('#lat').val()) || defaultLat;
                    var curLng = parseFloat($('#lng').val()) || defaultLng;
                    
                    if (!marker) {
                        marker = L.marker([curLat, curLng], {
                            draggable: true
                        }).addTo(map);

                        marker.on('dragend', function() {
                            var pos = marker.getLatLng();
                            $('#lat').val(pos.lat.toFixed(8));
                            $('#lng').val(pos.lng.toFixed(8));
                        });

                        $('#lat').val(curLat.toFixed(8));
                        $('#lng').val(curLng.toFixed(8));
                    }
                }
            }

            var activeType = $('#project_type').val();
            updateMapLayout(activeType);

            $('#project_type').on('change', function() {
                updateMapLayout($(this).val());
            });

            // Map Click Event
            map.on('click', function(e) {
                var type = $('#project_type').val();
                if (type === 'road') {
                    // Append new vertex
                    polylineLatLngs.push([e.latlng.lat, e.latlng.lng]);
                    renderPolyline();
                } else {
                    // Reposition marker
                    if (!marker) {
                        marker = L.marker([e.latlng.lat, e.latlng.lng], {
                            draggable: true
                        }).addTo(map);

                        marker.on('dragend', function() {
                            var pos = marker.getLatLng();
                            $('#lat').val(pos.lat.toFixed(8));
                            $('#lng').val(pos.lng.toFixed(8));
                        });
                    } else {
                        marker.setLatLng([e.latlng.lat, e.latlng.lng]);
                    }
                    
                    $('#lat').val(e.latlng.lat.toFixed(8));
                    $('#lng').val(e.latlng.lng.toFixed(8));
                }
            });

            // Drawing Tools click handlers
            $('#btn_undo_point').on('click', function() {
                if (polylineLatLngs.length > 0) {
                    polylineLatLngs.pop();
                    renderPolyline();
                }
            });

            $('#btn_clear_line').on('click', function() {
                if (confirm('Apakah Anda yakin ingin menghapus semua titik garis jalan?')) {
                    polylineLatLngs = [];
                    renderPolyline();
                }
            });

            setTimeout(function() {
                map.invalidateSize();
            }, 500);
        });
    </script>
@endpush
