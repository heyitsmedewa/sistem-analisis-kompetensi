<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Kompeten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $user = auth()->user();
        $isAdmin = $user->roles->contains('name', 'admin');
    @endphp

    <div class="app-shell">
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}"
                   class="brand-link">
                    <div class="brand-mark">K</div>
                    <div>
                        <div class="brand-name">KOMPETEN</div>
                        <div class="brand-caption">Career & Competency System</div>
                    </div>
                </a>
            </div>

            <nav class="sidebar-navigation">
                @if($isAdmin)
                    <div class="sidebar-section">Workspace</div>

                    <a href="{{ route('admin.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">◫</span>
                        Dashboard
                    </a>

                    <div class="sidebar-section">Manajemen</div>

                    <a href="{{ route('admin.job-postings.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.job-postings.*') ? 'active' : '' }}">
                        <span class="nav-icon">▤</span>
                        Data Lowongan
                    </a>

                    <a href="{{ route('admin.skills.verification.index') }}"
                       class="sidebar-link {{ request()->routeIs('admin.skills.verification.index') ? 'active' : '' }}">
                        <span class="nav-icon">✓</span>
                        Verifikasi Kompetensi
                    </a>

                    <a href="{{ route('admin.skills.verification.history') }}"
                       class="sidebar-link {{ request()->routeIs('admin.skills.verification.history') ? 'active' : '' }}">
                        <span class="nav-icon">◷</span>
                        Riwayat Verifikasi
                    </a>
                @else
                    <div class="sidebar-section">Workspace</div>

                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">◫</span>
                        Dashboard
                    </a>

                    <div class="sidebar-section">Pengembangan Karier</div>

                    <a href="{{ route('profile.show') }}"
                       class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="nav-icon">◎</span>
                        Profil Saya
                    </a>

                    <a href="{{ route('skills.index') }}"
                       class="sidebar-link {{ request()->routeIs('skills.*') ? 'active' : '' }}">
                        <span class="nav-icon">◇</span>
                        Kompetensi
                    </a>

                    <a href="{{ route('cv.index') }}"
                       class="sidebar-link {{ request()->routeIs('cv.*') ? 'active' : '' }}">
                        <span class="nav-icon">▤</span>
                        CV Saya
                    </a>

                    <a href="{{ route('matching.index') }}"
                       class="sidebar-link {{ request()->routeIs('matching.*') ? 'active' : '' }}">
                        <span class="nav-icon">⌕</span>
                        Analisis Kecocokan
                    </a>
                @endif
            </nav>

            <div class="sidebar-user">
                <div class="user-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="user-information">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">
                        {{ $isAdmin ? 'Administrator' : 'Mahasiswa' }}
                    </div>
                </div>
            </div>
        </aside>

        <main class="app-main">
            <header class="app-topbar">
                <div>
                    <div class="topbar-eyebrow">
                        {{ $isAdmin ? 'ADMINISTRATION' : 'STUDENT WORKSPACE' }}
                    </div>
                    <div class="topbar-title">
                        @yield('topbar-title', 'Kompeten')
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        Keluar <span>↗</span>
                    </button>
                </form>
            </header>

            {{-- Mobile navigation --}}
            <nav class="mobile-navigation">
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}"
                       class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.job-postings.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('admin.job-postings.*') ? 'active' : '' }}">
                        Lowongan
                    </a>
                    <a href="{{ route('admin.skills.verification.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('admin.skills.verification.index') ? 'active' : '' }}">
                        Verifikasi
                    </a>
                    <a href="{{ route('admin.skills.verification.history') }}"
                       class="mobile-nav-link {{ request()->routeIs('admin.skills.verification.history') ? 'active' : '' }}">
                        Riwayat
                    </a>
                @else
                    <a href="{{ route('dashboard') }}"
                       class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('profile.show') }}"
                       class="mobile-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        Profil
                    </a>
                    <a href="{{ route('skills.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('skills.*') ? 'active' : '' }}">
                        Kompetensi
                    </a>
                    <a href="{{ route('cv.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('cv.*') ? 'active' : '' }}">
                        CV
                    </a>
                    <a href="{{ route('matching.index') }}"
                       class="mobile-nav-link {{ request()->routeIs('matching.*') ? 'active' : '' }}">
                        Kecocokan
                    </a>
                @endif
            </nav>

            <div class="app-content">
                @hasSection('page-header')
                    @yield('page-header')
                @endif

                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom:20px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom:20px;">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>