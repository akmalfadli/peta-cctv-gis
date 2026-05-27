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
        <!-- Stats widgets -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $stats['total'] }}</h3>
                        <p>Total Proyek Pembangunan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-map"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $stats['jalan'] }}</h3>
                        <p>Pembangunan Jalan (Garis)</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-road"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $stats['gedung'] }}</h3>
                        <p>Gedung & Bangunan (Titik)</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-building"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</h3>
                        <p>Total Alokasi Anggaran</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
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
                                <a href="{{ ci_route('gis_pembangunan.create') }}" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah Proyek Baru</a>
                                <a href="{{ ci_route('petagis.index') }}" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-map"></i> Lihat Peta Publik</a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter panel -->
                    <div class="box-body" style="background: #f9f9f9; border-bottom: 1px solid #eee; margin-bottom: 15px; padding: 15px;">
                        <div class="row">
                            <div class="col-sm-4">
                                <label>Kategori Pembangunan</label>
                                <select id="filter_kategori" class="form-control select2">
                                    <option value="">Semua Kategori</option>
                                    <option value="infrastruktur">Pembangunan Infrastruktur Desa</option>
                                    <option value="pendidikan">Pembangunan Bidang Pendidikan</option>
                                    <option value="kesehatan">Pembangunan Bidang Kesehatan</option>
                                    <option value="ekonomi">Pembangunan Ekonomi Desa</option>
                                    <option value="lingkungan">Pembangunan Lingkungan & Kebencanaan</option>
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label>Tipe Geometri Peta</label>
                                <select id="filter_type" class="form-control select2">
                                    <option value="">Semua Tipe</option>
                                    <option value="road">Jalan (Garis / Polyline)</option>
                                    <option value="building">Gedung / Bangunan (Titik / Marker)</option>
                                </select>
                            </div>

                            <div class="col-sm-2" style="margin-top: 25px;">
                                <button type="button" id="btn_reset_filter" class="btn btn-default btn-sm btn-block"><i class="fa fa-undo"></i> Reset Filter</button>
                            </div>
                        </div>
                    </div>

                    <div class="box-body table-responsive">
                        <table id="pembangunan_table" class="table table-bordered table-striped table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th width="30">No</th>
                                    <th width="80">Foto</th>
                                    <th>Jenis Kegiatan</th>
                                    <th width="55">Tahun</th>
                                    <th>Sumber Dana</th>
                                    <th>Anggaran</th>
                                    <th>Lokasi / Volume</th>
                                    <th>Pelaksana</th>
                                    <th width="140">Kategori</th>
                                    <th width="120">Tipe Peta</th>
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
                    Apakah Anda yakin ingin menghapus data pembangunan desa ini? Tindakan ini tidak dapat dibatalkan dan semua data pemetaan geometri akan terhapus.
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
    <style>
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
    <script>
        $(document).ready(function() {
            var table = $('#pembangunan_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ ci_route('gis_pembangunan.datatables') }}",
                    type: "POST",
                    data: function(d) {
                        d.type = $('#filter_type').val();
                        d.kategori = $('#filter_kategori').val();
                        d.search = d.search.value;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'photo_view', name: 'photo', orderable: false, searchable: false },
                    { 
                        data: 'jenis_kegiatan', 
                        name: 'jenis_kegiatan',
                        render: function(data, type, row) {
                            return '<strong>' + data + '</strong>';
                        }
                    },
                    {
                        data: 'tahun_anggaran',
                        name: 'tahun_anggaran',
                        class: 'text-center',
                        render: function(data) {
                            return data ? '<span class="label label-default">' + data + '</span>' : '-';
                        }
                    },
                    { data: 'sumber_dana', name: 'sumber_dana' },
                    { data: 'anggaran', name: 'anggaran' },
                    { 
                        data: 'lokasi', 
                        name: 'lokasi',
                        render: function(data, type, row) {
                            return '<strong>Lokasi:</strong> ' + data + '<br><strong>Volume:</strong> ' + (row.volume || '-');
                        }
                    },
                    { data: 'pelaksana', name: 'pelaksana' },
                    { data: 'kategori', name: 'kategori', class: 'text-center' },
                    { data: 'type', name: 'type', class: 'text-center' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, class: 'text-center' }
                ]
            });

            // Trigger filters
            $('#filter_type, #filter_kategori').on('change', function() {
                table.draw();
            });

            // Reset filters
            $('#btn_reset_filter').on('click', function() {
                $('#filter_type').val('').trigger('change');
                $('#filter_kategori').val('').trigger('change');
            });

            // Delete modal handler
            $('#pembangunan_table').on('click', '.btn-delete-pembangunan', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).data('href') || $(this).attr('href');
                $('#btn_confirm_delete').attr('href', deleteUrl);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
