@extends('admin.layouts.index')

@section('title')
    <h1>{{ $title }}</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ site_url('hom_sid') }}"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="{{ site_url('gis_camera') }}">Daftar Kamera CCTV</a></li>
    <li class="active">{{ $title }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCategoryModal">
                            <i class="fa fa-plus"></i> Tambah Kategori Baru
                        </button>
                    </div>

                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Kategori</th>
                                    <th width="150" class="text-center">Jumlah Kamera CCTV</th>
                                    <th width="150" class="text-center">Tanggal Dibuat</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $index => $cat)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $cat->name }}</strong></td>
                                        <td class="text-center"><span class="label label-info">{{ $cat->cameras_count }} Kamera</span></td>
                                        <td class="text-center">{{ $cat->created_at ? $cat->created_at->format('d-m-Y H:i') : '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-primary btn-xs btn-edit-category" 
                                                    data-id="{{ $cat->id }}" 
                                                    data-name="{{ $cat->name }}" 
                                                    title="Edit Kategori">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            
                                            @if ($cat->cameras_count == 0)
                                                <button type="button" 
                                                        class="btn btn-danger btn-xs btn-delete-category" 
                                                        data-href="{{ ci_route('gis_category.delete', $cat->id) }}" 
                                                        title="Hapus Kategori">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-danger btn-xs disabled" 
                                                        title="Kategori tidak dapat dihapus karena masih dikaitkan dengan kamera CCTV" 
                                                        disabled>
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted" style="padding: 20px;">Belum ada data kategori GIS yang tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal: Add Category -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-tag"></i> Tambah Kategori GIS Baru</h4>
                </div>
                {!! form_open(ci_route('gis_category.store'), ['class' => 'form-horizontal']) !!}
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Nama Kategori <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Jalan Protokol" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Kategori</button>
                </div>
                {!! form_close() !!}
            </div>
        </div>
    </div>

    <!-- Modal: Edit Category -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Nama Kategori GIS</h4>
                </div>
                <form id="editCategoryForm" method="POST" class="form-horizontal">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Nama Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="name" id="edit_category_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Delete Category Confirmation -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header text-danger">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Konfirmasi</h4>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data kategori GIS ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <a href="#" id="btn_confirm_delete_category" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Edit trigger
            $('.btn-edit-category').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                
                $('#edit_category_name').val(name);
                $('#editCategoryForm').attr('action', "{{ site_url('gis_category/update') }}/" + id);
                $('#editCategoryModal').modal('show');
            });

            // Delete trigger
            $('.btn-delete-category').on('click', function() {
                var deleteUrl = $(this).data('href');
                $('#btn_confirm_delete_category').attr('href', deleteUrl);
                $('#deleteCategoryModal').modal('show');
            });
        });
    </script>
@endpush
