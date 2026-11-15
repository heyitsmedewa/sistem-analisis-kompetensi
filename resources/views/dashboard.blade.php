
@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('topbar-title', 'Dashboard Mahasiswa')

@section('page-header')
    <div style="margin-bottom:30px;">
        <div class="page-eyebrow">Student workspace</div>
        <h1 class="page-title">
            Selamat datang, {{ auth()->user()->name }}.
        </h1>
        <p class="page-description">
            Kenali kompetensimu, analisis peluang kerja, dan rencanakan pengembangan kariermu.
        </p>
    </div>
@endsection

@section('content')
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">

        <a href="{{ route('profile.show') }}" class="panel" style="padding:24px; color:inherit;">
            <div class="page-eyebrow">01 / Personal</div>
            <h2 class="section-title" style="margin-top:16px;">Profil Mahasiswa</h2>
            <p class="page-description">Kelola identitas dan informasi akademikmu.</p>
            <div style="margin-top:20px; color:var(--forest); font-size:13px; font-weight:700;">
                Buka profil →
            </div>
        </a>

        <a href="{{ route('skills.index') }}" class="panel" style="padding:24px; color:inherit;">
            <div class="page-eyebrow">02 / Competency</div>
            <h2 class="section-title" style="margin-top:16px;">Kompetensi</h2>
            <p class="page-description">Catat dan perbarui kemampuan yang kamu miliki.</p>
            <div style="margin-top:20px; color:var(--forest); font-size:13px; font-weight:700;">
                Kelola kompetensi →
            </div>
        </a>

        <a href="{{ route('cv.index') }}" class="panel" style="padding:24px; color:inherit;">
            <div class="page-eyebrow">03 / Documents</div>
            <h2 class="section-title" style="margin-top:16px;">CV Mahasiswa</h2>
            <p class="page-description">Unggah CV dan konfirmasi data hasil ekstraksi.</p>
            <div style="margin-top:20px; color:var(--forest); font-size:13px; font-weight:700;">
                Kelola CV →
            </div>
        </a>

        <a href="{{ route('matching.index') }}" class="panel" style="padding:24px; color:inherit;">
            <div class="page-eyebrow">04 / Career analysis</div>
            <h2 class="section-title" style="margin-top:16px;">Analisis Kecocokan</h2>
            <p class="page-description">Bandingkan kompetensimu dengan kebutuhan lowongan kerja.</p>
            <div style="margin-top:20px; color:var(--forest); font-size:13px; font-weight:700;">
                Mulai analisis →
            </div>
        </a>

    </div>

    <div class="panel" style="margin-top:24px; padding:24px;">
        <div class="page-eyebrow">Your next step</div>
        <h2 class="section-title" style="margin-top:8px;">Bangun kesiapan kariermu.</h2>
        <p class="page-description">
            Lengkapi profil, masukkan kompetensi, unggah CV, lalu gunakan
            Profile Matching untuk melihat kesesuaian dengan lowongan kerja.
        </p>
    </div>
@endsection
