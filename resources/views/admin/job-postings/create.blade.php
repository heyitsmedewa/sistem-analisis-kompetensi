```php
@extends('layouts.app')

@section('title', 'Tambah Lowongan')
@section('topbar-title', 'Tambah Lowongan')

@section('page-header')
    <div style="margin-bottom:28px;">
        <div class="page-eyebrow">Administration / Jobs / New entry</div>
        <h1 class="page-title">Tambah Lowongan.</h1>
        <p class="page-description">
            Masukkan informasi pekerjaan dan persyaratan kompetensi yang dibutuhkan.
        </p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.job-postings.store') }}">
        @csrf

        {{-- Informasi Pekerjaan --}}
        <section class="panel" style="margin-bottom:24px;">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">01 / Job information</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Informasi Pekerjaan
                    </h2>
                    <p class="page-description" style="margin-top:5px;">
                        Informasi dasar mengenai posisi dan perusahaan.
                    </p>
                </div>
            </div>

            <div class="panel-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
                    <div>
                        <label for="title" class="form-label">Judul Lowongan *</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            value="{{ old('title') }}"
                            maxlength="200"
                            required
                        >
                        @error('title')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="company" class="form-label">Nama Perusahaan</label>
                        <input
                            type="text"
                            id="company"
                            name="company"
                            class="form-control"
                            value="{{ old('company') }}"
                            maxlength="200"
                        >
                        @error('company')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="form-label">Lokasi</label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            class="form-control"
                            value="{{ old('location') }}"
                            maxlength="200"
                        >
                        @error('location')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="margin-top:22px;">
                    <label for="description" class="form-label">Deskripsi Pekerjaan</label>
                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        rows="6"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Persyaratan --}}
        <section class="panel" style="margin-bottom:24px;">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">02 / Requirements</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Persyaratan Pekerjaan
                    </h2>
                    <p class="page-description" style="margin-top:5px;">
                        Pendidikan dan pengalaman yang dibutuhkan untuk posisi ini.
                    </p>
                </div>
            </div>

            <div class="panel-body">
                <div style="margin-bottom:20px;">
                    <label for="education_requirement" class="form-label">
                        Persyaratan Pendidikan
                    </label>
                    <input
                        type="text"
                        id="education_requirement"
                        name="education_requirement"
                        class="form-control"
                        value="{{ old('education_requirement') }}"
                        maxlength="200"
                    >
                    @error('education_requirement')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="experience_requirement" class="form-label">
                        Persyaratan Pengalaman
                    </label>
                    <textarea
                        id="experience_requirement"
                        name="experience_requirement"
                        class="form-control"
                        rows="4"
                    >{{ old('experience_requirement') }}</textarea>
                    @error('experience_requirement')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Sumber Lowongan --}}
        <section class="panel">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">03 / Source</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Sumber Lowongan
                    </h2>
                    <p class="page-description" style="margin-top:5px;">
                        Catat asal informasi lowongan untuk keperluan penelusuran.
                    </p>
                </div>
            </div>

            <div class="panel-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
                    <div>
                        <label for="source_name" class="form-label">
                            Nama Sumber
                        </label>
                        <input
                            type="text"
                            name="source_name"
                            id="source_name"
                            class="form-control"
                            value="{{ old('source_name') }}"
                            placeholder="Contoh: LinkedIn, Instagram, JobStreet"
                        >
                        @error('source_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="source_url" class="form-label">
                            URL Sumber Lowongan
                        </label>
                        <input
                            type="url"
                            name="source_url"
                            id="source_url"
                            class="form-control"
                            value="{{ old('source_url') }}"
                            placeholder="https://..."
                        >
                        @error('source_url')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- Actions --}}
        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:24px;">
            <a href="{{ route('admin.job-postings.index') }}" class="btn-secondary">
                Batal
            </a>

            <button type="submit" class="btn-primary">
                Simpan dan Lanjutkan →
            </button>
        </div>
    </form>
@endsection
```