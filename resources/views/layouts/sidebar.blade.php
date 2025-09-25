<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="logo" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="logo" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

            <ul class="navbar-nav" id="navbar-nav">
                {{-- ===================== MENU UTAMA ===================== --}}
                <li class="menu-title"><span>Menu Utama</span></li>


                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i data-feather="home" class="icon-dual"></i>
                        <span>Dashboard</span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('postgres') ? 'active' : '' }}"
                        href="{{ route('postgres.index') }}">
                        <i class="bx bx-data mr-2"></i>
                        <span>Postgres DB</span>
                    </a>
                </li>


                {{-- ===================== PENGATURAN ===================== --}}
                <li class="menu-title"><span>Pengaturan</span></li>
                {{--  --}}
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>
