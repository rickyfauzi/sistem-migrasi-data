@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Home
        @endslot
        @slot('title')
            Dashboard
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4>Selamat Datang di Dashboard</h4>
                    <p>Gunakan menu di sisi kiri untuk mengimpor data dari PostgreSQL dan mengekspor ke MySQL.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
