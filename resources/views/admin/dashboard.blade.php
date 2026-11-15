```php
@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('topbar-title', 'Dashboard Administrator')

@section('page-header')
    <div style="margin-bottom:30px;">
        <div class="page-eyebrow">Administration / Overview</div>
        <h1 class="page-title">Dashboard Admin.</h1>
        <p class="page-description">
            Pantau aktivitas sistem, kelola lowongan kerja, dan tinjau kompetensi mahasiswa.
        </p>
    </div>
@endsection

@section('content')
    {{-- Ringkasan Sistem --}}
    <section>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px;">
            <h2 class="section-title">Ringkasan sistem</h2>
            <span class="page-eyebrow">System overview</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:16px;">
            <div class="stat-card">
                <div class="stat-label">Total lowongan</div>
                <div class="stat-value">{{ $totalJobs }}</div>
                <p class="page-description">Seluruh lowongan tersimpan</p>
            </div>

            <div class="stat-card">
                <div class="stat-label">Lowongan aktif</div>
                <div class="stat-value" style="color:var(--forest);">
                    {{ $activeJobs }}
                </div>
                <p class="page-description">Lowongan yang sedang aktif</p>
            </div>

            <div class="stat-card">
                <div class="stat-label">Menunggu verifikasi</div>
                <div class="stat-value">{{ $pendingVerifications }}</div>
                <p class="page-description">Kompetensi perlu ditinjau</p>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kompetensi disetujui</div>
                <div class="stat-value">{{ $approvedSkills }}</div>
                <p class="page-description">Kompetensi berstatus approved</p>
            </div>
        </div>
    </section>

    {{-- Menu Pengelolaan --}}
    <section style="margin-top:36px;">
        <div style="margin-bottom:16px;">
            <div class="page-eyebrow">Workspace</div>
            <h2 class="section-title" style="margin-top:5px;">Menu pengelolaan</h2>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px;">
            <a href="{{ route('admin.skills.verification.index') }}"
               class="panel"
               style="padding:24px; color:inherit; text-decoration:none;">
                <div class="page-eyebrow">01 / Review</div>
                <h3 class="section-title" style="margin-top:18px;">
                    Verifikasi kompetensi
                </h3>
                <p class="page-description">
                    Tinjau level kompetensi mahasiswa sebelum digunakan dalam analisis.
                </p>
                <div style="margin-top:22px; color:var(--forest); font-size:13px; font-weight:700;">
                    Buka verifikasi →
                </div>
            </a>

            <a href="{{ route('admin.skills.verification.history') }}"
               class="panel"
               style="padding:24px; color:inherit; text-decoration:none;">
                <div class="page-eyebrow">02 / Records</div>
                <h3 class="section-title" style="margin-top:18px;">
                    Riwayat verifikasi
                </h3>
                <p class="page-description">
                    Lihat kompetensi mahasiswa yang sudah disetujui atau ditolak.
                </p>
                <div style="margin-top:22px; color:var(--forest); font-size:13px; font-weight:700;">
                    Buka riwayat →
                </div>
            </a>

            <a href="{{ route('admin.job-postings.index') }}"
               class="panel"
               style="padding:24px; color:inherit; text-decoration:none;">
                <div class="page-eyebrow">03 / Database</div>
                <h3 class="section-title" style="margin-top:18px;">
                    Manajemen lowongan
                </h3>
                <p class="page-description">
                    Kelola lowongan, persyaratan kompetensi, dan status aktif.
                </p>
                <div style="margin-top:22px; color:var(--forest); font-size:13px; font-weight:700;">
                    Buka lowongan →
                </div>
            </a>

            <a href="{{ route('admin.job-postings.create') }}"
               class="panel"
               style="padding:24px; color:inherit; text-decoration:none;">
                <div class="page-eyebrow">04 / New entry</div>
                <h3 class="section-title" style="margin-top:18px;">
                    Tambah lowongan
                </h3>
                <p class="page-description">
                    Masukkan lowongan kerja baru ke dalam sistem.
                </p>
                <div style="margin-top:22px; color:var(--forest); font-size:13px; font-weight:700;">
                    Buat lowongan →
                </div>
            </a>
        </div>
    </section>

    <div class="panel" style="margin-top:36px; padding:22px 24px;">
        <div class="page-eyebrow">Kompeten / Administration</div>
        <p class="page-description" style="margin-top:8px;">
            Sistem Analisis Kesesuaian Kompetensi Mahasiswa terhadap Lowongan Kerja.
        </p>
    </div>
@endsection
```