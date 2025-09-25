@extends('layouts.master')

@section('title')
    Pratinjau Tabel: {{ $table }}
@endsection

@section('css')
    <!--datatable css-->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!--datatable responsive css-->
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />
    <!--datatable buttons css-->
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!--datatable fixedheader css-->
    <link href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css" rel="stylesheet"
        type="text/css" />

    <style>
        /* Custom DataTable Styles */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 0.5rem 0;
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }

        .dataTables_wrapper .dt-buttons {
            margin-bottom: 0.5rem;
        }

        .dt-button {
            margin-right: 0.25rem !important;
            margin-bottom: 0.25rem !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
            border-radius: 0.375rem !important;
        }

        /* Table Enhancement */
        #table-preview {
            font-size: 0.875rem;
        }

        #table-preview thead th {
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            border-color: #5a6c8a;
        }

        #table-preview tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.1);
        }

        #table-preview tbody td {
            vertical-align: middle;
            word-break: break-word;
            max-width: 200px;
        }

        /* Responsive Collapse Button */
        .dtr-control {
            cursor: pointer;
            text-align: center;
        }

        .dtr-control:before {
            color: #667eea;
            font-weight: bold;
        }

        /* Card Enhancement */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        /* Loader */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            color: #495057 !important;
        }

        /* Status Indicators */
        .table-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .stat-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header .flex-shrink-0 {
                width: 100%;
                margin-top: 0.5rem;
            }

            .card-header .flex-shrink-0 .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .card-header .flex-shrink-0 form {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    {{-- Komponen Judul Halaman (Breadcrumb) --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    <i class="ri-database-2-line me-2"></i>
                    Pratinjau Tabel: <strong class="text-primary">{{ $table }}</strong>
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('postgres.index') }}">Daftar Tabel</a></li>
                        <li class="breadcrumb-item active">{{ $table }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    {{-- Akhir Komponen Judul Halaman --}}

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-table-2 me-2"></i>Data dari tabel '<span
                            class="text-primary">{{ $table }}</span>'
                    </h5>
                    <div class="flex-shrink-0 mt-2 mt-sm-0 d-flex gap-2 flex-wrap">
                        <a href="{{ route('postgres.index') }}" class="btn btn-soft-secondary btn-sm">
                            <i class="ri-arrow-go-back-line align-bottom"></i> Kembali
                        </a>

                        <form action="{{ route('postgres.export') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="table_name" value="{{ $table }}">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-database-2-line align-bottom"></i> Export ke MySQL
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if ($rows->isNotEmpty())
                        @php
                            // Mengambil nama kolom dari baris pertama secara dinamis
                            $columns = array_keys((array) $rows->first());
                            $totalRows = $rows->count();
                        @endphp

                        {{-- Table Statistics --}}


                        <div class="table-responsive">
                            <table id="table-preview" class="table table-bordered table-striped table-hover nowrap"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="all">No</th>
                                        @foreach ($columns as $index => $column)
                                            <th class="{{ $index < 3 ? 'all' : ($index < 6 ? 'min-tablet-l' : 'none') }}">
                                                {{ ucwords(str_replace(['_', '-'], ' ', $column)) }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $key => $row)
                                        <tr>
                                            <td class="text-center"><span
                                                    class="badge bg-primary">{{ $key + 1 }}</span></td>
                                            @foreach ($columns as $column)
                                                <td>
                                                    @if (is_null($row->{$column}))
                                                        <span class="text-muted fst-italic">NULL</span>
                                                    @elseif (is_bool($row->{$column}))
                                                        <span
                                                            class="badge {{ $row->{$column} ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $row->{$column} ? 'TRUE' : 'FALSE' }}
                                                        </span>
                                                    @elseif (is_numeric($row->{$column}))
                                                        <span
                                                            class="text-info fw-semibold">{{ number_format($row->{$column}, 0, ',', '.') }}</span>
                                                    @elseif (filter_var($row->{$column}, FILTER_VALIDATE_EMAIL))
                                                        <a href="mailto:{{ $row->{$column} }}"
                                                            class="text-decoration-none">{{ $row->{$column} }}</a>
                                                    @elseif (filter_var($row->{$column}, FILTER_VALIDATE_URL))
                                                        <a href="{{ $row->{$column} }}" target="_blank"
                                                            class="text-decoration-none">{{ Str::limit($row->{$column}, 30) }}</a>
                                                    @else
                                                        {{ Str::limit($row->{$column}, 50) }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-4">
                                    <i class="ri-database-line"></i>
                                </div>
                            </div>
                            <h5 class="mb-1">Tidak Ada Data</h5>
                            <p class="text-muted mb-0">Tabel <strong>{{ $table }}</strong> tidak memiliki data untuk
                                ditampilkan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div><!--end col-->
    </div><!--end row-->
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

    {{-- DataTables Assets --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- SweetAlert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable dengan konfigurasi lengkap
            var table = $('#table-preview').DataTable({
                // Layout dan tampilan
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"B>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',

                // Buttons konfigurasi
                buttons: [{
                        extend: 'copy',
                        text: '<i class="ri-file-copy-line"></i> Copy',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="ri-file-text-line"></i> CSV',
                        className: 'btn btn-outline-success btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="ri-file-excel-line"></i> Excel',
                        className: 'btn btn-outline-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="ri-file-pdf-line"></i> PDF',
                        className: 'btn btn-outline-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line"></i> Print',
                        className: 'btn btn-outline-primary btn-sm'
                    }
                ],

                // Responsive settings
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },

                // Column definitions
                columnDefs: [{
                        className: 'dtr-control',
                        orderable: false,
                        targets: 0
                    },
                    {
                        responsivePriority: 1,
                        targets: [0, 1]
                    },
                    {
                        responsivePriority: 2,
                        targets: -1
                    }
                ],

                // Length menu
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'Semua']
                ],

                // Page length
                pageLength: 25,

                // Processing
                processing: true,

                // Language
                language: {
                    "sProcessing": "Sedang memproses...",
                    "sLengthMenu": "Tampilkan _MENU_ entri",
                    "sZeroRecords": "Tidak ditemukan data yang sesuai",
                    "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "sInfoPostFix": "",
                    "sSearch": "Cari:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "Pertama",
                        "sPrevious": "Sebelumnya",
                        "sNext": "Selanjutnya",
                        "sLast": "Terakhir"
                    }
                },

                // Fixed header
                fixedHeader: true,

                // Order
                order: [
                    [1, 'asc']
                ],

                // Scrolling
                scrollX: true,
                scrollCollapse: true
            });

            // Auto refresh setiap 30 detik (opsional)
            // setInterval(function() {
            //     table.ajax.reload(null, false);
            // }, 30000);

            // Event listener untuk responsive details
            table.on('responsive-display', function(e, datatable, row, showHide, update) {
                if (showHide) {
                    // Tambahkan animasi atau efek khusus jika diperlukan
                    console.log('Row details shown');
                }
            });

            // Highlight search results
            table.on('search.dt', function() {
                var searchVal = table.search();
                if (searchVal) {
                    table.rows().every(function() {
                        $(this.node()).addClass('search-highlight');
                    });
                } else {
                    table.rows().every(function() {
                        $(this.node()).removeClass('search-highlight');
                    });
                }
            });
        });

        // Export confirmation
        $(document).on('submit', 'form[action="{{ route('postgres.export') }}"]', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Export',
                text: 'Apakah Anda yakin ingin mengekspor tabel "{{ $table }}" ke MySQL?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Export!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve();
                        }, 1000);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>

    <!-- Success/Error Notifications -->
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            Swal.fire({
                title: 'Perhatian!',
                text: "{{ session('warning') }}",
                icon: 'warning',
                confirmButtonColor: '#f39c12',
                confirmButtonText: 'Mengerti'
            });
        </script>
    @endif

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
