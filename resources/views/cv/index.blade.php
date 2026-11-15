
@extends('layouts.app')

@section('title', 'CV Mahasiswa')
@section('topbar-title', 'CV Mahasiswa')

@section('page-header')
    <div style="margin-bottom:28px;">
        <div class="page-eyebrow">Career workspace / Documents</div>
        <h1 class="page-title">CV Mahasiswa.</h1>
        <p class="page-description">
            Unggah dan kelola CV untuk mengekstrak informasi pendidikan, pengalaman, proyek, dan kompetensi.
        </p>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <strong>Unggah CV belum berhasil.</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0, 1fr) 280px;gap:20px;align-items:start;">
        <div style="display:flex;flex-direction:column;gap:20px;">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <div class="page-eyebrow">Document upload</div>
                        <h2 class="section-title" style="margin-top:6px;">Unggah CV</h2>
                        <p class="page-description" style="margin-top:5px;">
                            Gunakan CV terbaru agar informasi yang dianalisis lebih relevan.
                        </p>
                    </div>
                    <span class="skill-badge skill-badge-info">CV / Resume</span>
                </div>

                <div class="panel-body">
                    <form action="{{ route('cv.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <label for="cv" class="form-label">
                            Pilih file CV
                        </label>

                        <div class="upload-area">
                            <div class="upload-icon">
                                <icon name="file-up" size="28" color="#a78bfa" />
                            </div>

                            <div style="font-size:14px;font-weight:650;color:var(--ink);">
                                Pilih dokumen dari perangkatmu
                            </div>
                            <p style="margin:6px 0 16px;color:var(--muted);font-size:12px;">
                                Pastikan dokumen dapat dibaca dengan jelas.
                            </p>

                            <input
                                type="file"
                                name="cv"
                                id="cv"
                                class="form-control"
                                accept=".pdf,.docx"
                                required
                            >

                            <div style="margin-top:10px;color:var(--muted);font-size:11px;">
                                Format yang didukung: PDF atau DOCX · Maksimal 5 MB
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;margin-top:20px;">
                            <button type="submit" class="btn-primary">
                                <icon name="upload" size="15" />
                                Unggah CV →
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div>
                        <div class="page-eyebrow">Document archive</div>
                        <h2 class="section-title" style="margin-top:6px;">Riwayat CV</h2>
                        <p class="page-description" style="margin-top:5px;">
                            Dokumen CV yang pernah kamu unggah.
                        </p>
                    </div>

                    <span class="skill-badge skill-badge-neutral">
                        {{ $cvs->count() }} dokumen
                    </span>
                </div>

                <div class="panel-body">
                    @forelse ($cvs as $cv)
                        <article class="cv-item">
                            <div class="cv-file-icon">
                                <icon name="file-text" size="22" color="#a78bfa" />
                            </div>

                            <div class="cv-information">
                                <div class="cv-heading">
                                    <div class="cv-filename">
                                        {{ $cv->original_filename }}
                                    </div>

                                    @if ($cv->extraction_status === 'completed')
                                        <span class="skill-badge skill-badge-success">Selesai</span>
                                    @elseif ($cv->extraction_status === 'failed')
                                        <span class="skill-badge skill-badge-danger">Gagal</span>
                                    @elseif ($cv->extraction_status === 'processing')
                                        <span class="skill-badge skill-badge-warning">Diproses</span>
                                    @else
                                        <span class="skill-badge skill-badge-neutral">
                                            {{ ucfirst($cv->extraction_status) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="cv-metadata">
                                    <span>
                                        <icon name="hard-drive" size="13" />
                                        {{ number_format($cv->file_size / 1024, 1) }} KB
                                    </span>
                                    <span>
                                        <icon name="calendar" size="13" />
                                        {{ $cv->uploaded_at?->format('d-m-Y H:i') ?? '-' }}
                                    </span>
                                </div>

                                <div style="margin-top:14px;">
                                    @if ($cv->extraction_status === 'completed')
                                        <a href="{{ route('cv.show', $cv) }}" class="btn-primary btn-small">
                                            Lihat Hasil
                                            <icon name="arrow-up-right" size="13" />
                                        </a>
                                    @elseif ($cv->extraction_status === 'failed')
                                        <a href="{{ route('cv.show', $cv) }}" class="btn-secondary btn-small">
                                            Lihat Detail
                                            <icon name="arrow-up-right" size="13" />
                                        </a>
                                    @else
                                        <button type="button" class="btn-secondary btn-small" disabled>
                                            <icon name="clock" size="13" />
                                            Hasil Belum Tersedia
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div style="padding:38px 20px;text-align:center;">
                            <div style="margin-bottom:12px;">
                                <icon name="files" size="34" color="#64748b" />
                            </div>
                            <h3 style="margin:0 0 7px;font-size:14px;color:var(--ink);">
                                Belum ada CV
                            </h3>
                            <p style="margin:0;color:var(--muted);font-size:12px;">
                                CV yang kamu unggah akan muncul di bagian ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside style="display:flex;flex-direction:column;gap:16px;">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <div class="page-eyebrow">Extraction process</div>
                        <h2 class="section-title" style="margin-top:6px;">Alur Pemrosesan</h2>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="process-step">
                        <span class="process-number">01</span>
                        <div>
                            <div class="process-title">Unggah CV</div>
                            <p>Masukkan dokumen CV dalam format yang didukung.</p>
                        </div>
                    </div>

                    <div class="process-step">
                        <span class="process-number">02</span>
                        <div>
                            <div class="process-title">Ekstraksi Informasi</div>
                            <p>Sistem memproses informasi dari dokumen CV.</p>
                        </div>
                    </div>

                    <div class="process-step">
                        <span class="process-number">03</span>
                        <div>
                            <div class="process-title">Periksa Hasil</div>
                            <p>Tinjau informasi hasil ekstraksi yang tersedia.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-body">
                    <div class="page-eyebrow">Upload guidelines</div>
                    <h3 style="margin:8px 0;font-size:14px;color:var(--ink);">
                        Tips sebelum mengunggah
                    </h3>
                    <ul style="margin:10px 0 0;padding-left:18px;color:var(--muted);font-size:12px;line-height:1.9;">
                        <li>Gunakan CV dengan informasi terbaru.</li>
                        <li>Pastikan teks dalam dokumen terbaca.</li>
                        <li>Periksa kembali informasi kontak dan pengalaman.</li>
                        <li>Gunakan file PDF atau DOCX maksimal 5 MB.</li>
                    </ul>
                </div>
            </section>
        </aside>
    </div>

    <style>
        .upload-area {
            padding:24px;
            border:1px dashed var(--line);
            border-radius:10px;
            background:var(--surface-raised);
            text-align:center;
        }

        .upload-icon {
            width:54px;
            height:54px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 14px;
            border:1px solid rgba(167,139,250,.2);
            border-radius:12px;
            background:rgba(124,58,237,.12);
        }

        .upload-area .form-control {
            max-width:440px;
            margin:0 auto;
            text-align:left;
        }

        .cv-item {
            display:flex;
            align-items:flex-start;
            gap:14px;
            padding:18px 0;
            border-bottom:1px solid var(--line);
        }

        .cv-item:first-child {
            padding-top:0;
        }

        .cv-item:last-child {
            padding-bottom:0;
            border-bottom:0;
        }

        .cv-file-icon {
            width:42px;
            height:42px;
            flex-shrink:0;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid rgba(167,139,250,.2);
            border-radius:9px;
            background:rgba(124,58,237,.1);
        }

        .cv-information {
            flex:1;
            min-width:0;
        }

        .cv-heading {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
        }

        .cv-filename {
            min-width:0;
            overflow-wrap:anywhere;
            color:var(--ink);
            font-size:13px;
            font-weight:650;
        }

        .cv-metadata {
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:8px;
            color:var(--muted);
            font-size:11px;
        }

        .cv-metadata span {
            display:inline-flex;
            align-items:center;
            gap:5px;
        }

        .process-step {
            display:flex;
            gap:12px;
            padding-bottom:20px;
            position:relative;
        }

        .process-step:last-child {
            padding-bottom:0;
        }

        .process-number {
            width:28px;
            height:28px;
            flex-shrink:0;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid rgba(167,139,250,.25);
            border-radius:7px;
            background:rgba(124,58,237,.12);
            color:var(--purple-light);
            font-size:10px;
            font-weight:750;
        }

        .process-title {
            color:var(--ink);
            font-size:12px;
            font-weight:700;
        }

        .process-step p {
            margin:4px 0 0;
            color:var(--muted);
            font-size:11px;
            line-height:1.7;
        }

        .btn-small {
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:7px 11px;
            font-size:11px;
            text-decoration:none;
        }

        @media (max-width: 1050px) {
            .cv-page-layout {
                grid-template-columns:1fr !important;
            }
        }

        @media (max-width: 520px) {
            .upload-area {
                padding:18px 12px;
            }

            .cv-item {
                gap:10px;
            }
        }
    </style>
@endsection
