@include('admin.layouts.components.asset_datatables')
@extends('admin.layouts.index')

@section('title')
    <h1>{{ $title }}</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ site_url('hom_sid') }}"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">{{ $title }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    
    <section class="content">
        <!-- 1. Stats widgets -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $stats['total'] }}</h3>
                        <p>Total Kamera CCTV</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-video-camera"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $stats['online'] }}</h3>
                        <p>Kamera Online</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>{{ $stats['offline'] }}</h3>
                        <p>Kamera Offline</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-gray">
                    <div class="inner">
                        <h3>{{ $stats['inactive'] }}</h3>
                        <p>Kamera Nonaktif</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <div class="row">
                            <div class="col-sm-8">
                                <a href="{{ ci_route('cctv_admin.create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Kamera Baru</a>
                                <a href="{{ ci_route('cctv_admin.health_check') }}" class="btn btn-info btn-sm" title="Jalankan pengecekan status online/offline untuk semua kamera"><i class="fa fa-refresh"></i> Jalankan Cek Kesehatan</a>
                                <a href="{{ ci_route('cctv_admin.settings') }}" class="btn btn-warning btn-sm" title="Pengaturan Cuaca OpenWeatherMap"><i class="fa fa-cloud"></i> Pengaturan Cuaca</a>
                                <a href="{{ ci_route('cctv.index') }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-map"></i> Lihat Peta Publik</a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter panel -->
                    <div class="box-body" style="background: #f9f9f9; border-bottom: 1px solid #eee; margin-bottom: 15px; padding: 15px;">
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Kategori</label>
                                <select id="filter_category" class="form-control select2">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-sm-3">
                                <label>Kesehatan Status</label>
                                <select id="filter_status" class="form-control select2">
                                    <option value="">Semua Status</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <label>Status Aktif</label>
                                <select id="filter_active" class="form-control select2">
                                    <option value="">Semua</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <label>Akses Publik</label>
                                <select id="filter_public" class="form-control select2">
                                    <option value="">Semua</option>
                                    <option value="1">Publik</option>
                                    <option value="0">Privat</option>
                                </select>
                            </div>

                            <div class="col-sm-2" style="margin-top: 25px;">
                                <button type="button" id="btn_reset_filter" class="btn btn-default btn-sm btn-block"><i class="fa fa-undo"></i> Reset Filter</button>
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive">
                        <table id="cctv_table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="30">No</th>
                                    <th width="80">Thumbnail</th>
                                    <th>Nama Kamera</th>
                                    <th>Kategori</th>
                                    <th>Stream Type</th>
                                    <th width="100">Publik/Privat</th>
                                    <th width="100">Aktif/Non</th>
                                    <th width="100">Status Kesehatan</th>
                                    <th width="80" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-danger"><i class="fa fa-exclamation-triangle"></i> Konfirmasi</h4>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data kamera CCTV ini? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <a href="#" id="btn_confirm_delete" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <!-- CSS for Toggle Buttons (Bootstrap Toggle) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <style>
        .toggle.btn-size-mini {
            min-height: 18px !important;
            height: 18px !important;
            min-width: 60px !important;
            width: 60px !important;
            border-radius: 9px !important;
        }
        .toggle-group label.btn {
            padding-top: 0px !important;
            padding-bottom: 0px !important;
            line-height: 16px !important;
            font-size: 8px !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
        }
        .toggle-handle {
            padding-top: 0px !important;
            padding-bottom: 0px !important;
            line-height: 16px !important;
        }
        /* Fix AdminLTE Small-Box FontAwesome 6 Icon Overflow Bug */
        .small-box {
            overflow: hidden !important;
            position: relative;
        }
        .small-box .icon {
            top: -10px !important;
            right: 10px !important;
            opacity: 0.15;
            transition: all 0.3s ease-in-out;
        }
        .small-box:hover .icon {
            transform: scale(1.1);
            opacity: 0.25;
        }
    </style>
@endpush

@push('scripts')
    <!-- JS for Toggle Buttons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initializing Serverside DataTables
            var table = $('#cctv_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ ci_route('cctv_admin.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d.category_id = $('#filter_category').val();
                        d.status = $('#filter_status').val();
                        d.is_active = $('#filter_active').val();
                        d.is_public = $('#filter_public').val();
                        d.search = d.search.value; // Map search input appropriately
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'thumbnail_view', name: 'thumbnail', orderable: false, searchable: false },
                    { 
                        data: 'name', 
                        name: 'name',
                        render: function(data, type, row) {
                            return '<strong>' + data + '</strong>' + 
                                   (row.description ? '<br><small class="text-muted">' + row.description + '</small>' : '') +
                                   '<br><small class="text-info"><i class="fa fa-map-marker"></i> Lat: ' + row.latitude + ', Lng: ' + row.longitude + '</small>';
                        }
                    },
                    { data: 'category.name', name: 'category_id', defaultContent: '<span class="text-muted">Tanpa Kategori</span>' },
                    { data: 'stream_type', name: 'stream_type' },
                    { data: 'visibility', name: 'is_public', orderable: false, searchable: false },
                    { data: 'active_status', name: 'is_active', orderable: false, searchable: false },
                    { data: 'health_status', name: 'status', orderable: false, searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, class: 'text-center' }
                ],
                drawCallback: function() {
                    // Re-initialize bootstrap toggle switch on draw callback
                    $('.toggle-public, .toggle-active').bootstrapToggle({
                        size: 'mini',
                        width: '60'
                    });
                }
            });

            // Trigger filters
            $('#filter_category, #filter_status, #filter_active, #filter_public').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#btn_reset_filter').on('click', function() {
                $('#filter_category').val('').trigger('change');
                $('#filter_status').val('').trigger('change');
                $('#filter_active').val('').trigger('change');
                $('#filter_public').val('').trigger('change');
            });

            // AJAX Toggle Public/Private
            $('#cctv_table').on('change', '.toggle-public', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ site_url('cctv_admin/toggle_public') }}/" + id,
                    type: "POST",
                    dataType: "JSON",
                    error: function() {
                        alert('Gagal memperbarui status visibilitas publik/privat.');
                    }
                });
            });

            // AJAX Toggle Active/Inactive
            $('#cctv_table').on('change', '.toggle-active', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ site_url('cctv_admin/toggle_active') }}/" + id,
                    type: "POST",
                    dataType: "JSON",
                    error: function() {
                        alert('Gagal memperbarui status aktif/nonaktif.');
                    }
                });
            });

            // Delete modal handler
            $('#cctv_table').on('click', '.btn-delete-cctv', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).data('href') || $(this).attr('href');
                $('#btn_confirm_delete').attr('href', deleteUrl);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
