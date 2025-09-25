@extends('layouts.master')

@section('title')
    Daftar Tabel PostgreSQL
@endsection

@section('css')
    <!--datatable css-->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!--datatable responsive css-->
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    {{-- Komponen Judul Halaman (Breadcrumb) --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Manajemen Data PostgreSQL</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('root') }}">Dashboard</a></li> --}}
                        <li class="breadcrumb-item active">Daftar Tabel PostgreSQL</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    {{-- Akhir Komponen Judul Halaman --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tabel dari Database Source (PostgreSQL)</h5>
                </div>
                <div class="card-body">
                    <table id="postgres-tables-list"
                        class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Tabel</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tables as $index => $t)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $t->table_name }}</strong></td>
                                    <td>
                                        <a href="{{ route('postgres.show', $t->table_name) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="ri-eye-line align-middle"></i> Lihat Data
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        Tidak ada tabel yang ditemukan pada koneksi database 'pgsql_source'.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            $('#postgres-tables-list').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json" // Opsi: terjemahan bahasa Indonesia
                }
            });
        });
    </script>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
