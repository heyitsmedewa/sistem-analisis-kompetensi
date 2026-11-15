@extends('layouts.app')

@section('title', 'Profil Mahasiswa')
@section('topbar-title', 'Profil Mahasiswa')

@section('page-header')
    <div class="profile-page-header">
        <div>
            <div class="page-eyebrow">Student Workspace / Personal Information</div>
            <h1 class="page-title">Profil Mahasiswa.</h1>
            <p class="page-description">
                Kelola informasi akademik untuk mendukung analisis kompetensi
                dan rekomendasi karier.
            </p>
        </div>

        <div class="profile-header-tag">
            <span class="profile-status-dot"></span>
            Data Akademik
        </div>
    </div>
@endsection

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger profile-alert">
            <strong>Ada data yang perlu diperbaiki.</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-layout">

        {{-- Profile Identity --}}
        <aside class="profile-overview panel">
            <div class="profile-overview-top">
                <div class="profile-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="profile-overview-name">
                    {{ auth()->user()->name }}
                </div>

                <div class="profile-overview-role">
                    Mahasiswa
                </div>
            </div>

            <div class="profile-overview-divider"></div>

            <div class="profile-overview-info">
                <div class="profile-info-label">NIM</div>
                <div class="profile-info-value">
                    {{ $profile->student_number ?: 'Belum diisi' }}
                </div>

                <div class="profile-info-label">Perguruan Tinggi</div>
                <div class="profile-info-value">
                    {{ $profile->institution ?: 'Belum diisi' }}
                </div>

                <div class="profile-info-label">Program Studi</div>
                <div class="profile-info-value">
                    {{ $profile->study_program ?: 'Belum diisi' }}
                </div>
            </div>

            <div class="profile-overview-note">
                <span>ⓘ</span>
                Informasi akademik digunakan sebagai konteks dalam analisis
                kesesuaian kompetensi.
            </div>
        </aside>

        {{-- Profile Form --}}
        <section class="panel profile-form-panel">

            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">01 / Academic Profile</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Informasi Akademik
                    </h2>
                    <p class="page-description" style="margin-top:5px;">
                        Pastikan informasi yang dimasukkan sesuai dengan data
                        akademik kamu.
                    </p>
                </div>
            </div>

            <div class="panel-body">

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="profile-form-grid">

                        {{-- Nama --}}
                        <div class="profile-field profile-field-full">
                            <label class="form-label" for="student_name">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                id="student_name"
                                class="form-control"
                                value="{{ auth()->user()->name }}"
                                disabled
                            >

                            <div class="form-hint">
                                Nama diambil dari akun pengguna dan tidak dapat
                                diubah melalui halaman ini.
                            </div>
                        </div>

                        {{-- NIM --}}
                        <div class="profile-field">
                            <label for="student_number" class="form-label">
                                Nomor Induk Mahasiswa (NIM)
                            </label>

                            <input
                                type="text"
                                id="student_number"
                                name="student_number"
                                class="form-control"
                                value="{{ old('student_number', $profile->student_number) }}"
                                placeholder="Masukkan NIM"
                            >

                            @error('student_number')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Education Level --}}
                        <div class="profile-field">
                            <label for="education_level" class="form-label">
                                Jenjang Pendidikan
                            </label>

                            <select
                                id="education_level"
                                name="education_level"
                                class="form-control"
                            >
                                <option value="">Pilih jenjang pendidikan</option>

                                @foreach (['D3', 'D4', 'S1', 'S2', 'S3'] as $level)
                                    <option
                                        value="{{ $level }}"
                                        @selected(old('education_level', $profile->education_level) === $level)
                                    >
                                        {{ $level }}
                                    </option>
                                @endforeach
                            </select>

                            @error('education_level')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Institution --}}
                        <div class="profile-field profile-field-full">
                            <label for="institution" class="form-label">
                                Perguruan Tinggi
                            </label>

                            <input
                                type="text"
                                id="institution"
                                name="institution"
                                class="form-control"
                                value="{{ old('institution', $profile->institution) }}"
                                placeholder="Nama universitas atau perguruan tinggi"
                            >

                            @error('institution')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Study Program --}}
                        <div class="profile-field profile-field-full">
                            <label for="study_program" class="form-label">
                                Program Studi
                            </label>

                            <input
                                type="text"
                                id="study_program"
                                name="study_program"
                                class="form-control"
                                value="{{ old('study_program', $profile->study_program) }}"
                                placeholder="Contoh: Informatika"
                            >

                            @error('study_program')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="profile-form-footer">
                        <div class="profile-save-note">
                            Pastikan seluruh informasi sudah benar sebelum
                            menyimpan perubahan.
                        </div>

                        <button type="submit" class="btn-primary">
                            Simpan Profil <span>→</span>
                        </button>
                    </div>

                </form>
            </div>
        </section>

    </div>
@endsection