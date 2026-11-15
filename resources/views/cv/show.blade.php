
@extends('layouts.app')

@section('title', 'Detail Hasil Ekstraksi CV')
@section('topbar-title', 'Detail Hasil Ekstraksi CV')

@php
    $data = $extraction?->confirmed_data
        ?? $extraction?->raw_response
        ?? [];

    $sections = [
        'educations' => 'Pendidikan',
        'experiences' => 'Pengalaman',
        'hard_skills' => 'Hard Skills',
        'soft_skills' => 'Soft Skills',
        'languages' => 'Bahasa',
        'organizations' => 'Organisasi',
        'certifications' => 'Sertifikasi',
        'projects' => 'Proyek',
        'achievements' => 'Pencapaian',
        'missing_or_uncertain' => 'Informasi yang Perlu Diperiksa',
    ];
@endphp

@section('page-header')
    <div style="margin-bottom:24px;">
        <div class="page-eyebrow">Career workspace / CV details</div>
        <h1 class="page-title">Detail Ekstraksi CV.</h1>
        <p class="page-description" style="overflow-wrap:anywhere;">
            {{ $cv->original_filename }}
        </p>
    </div>
@endsection

@section('content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('cv.index') }}" class="btn-secondary cv-back-link">
            <icon name="arrow-left" size="15" />
            Kembali ke daftar CV
        </a>
    </div>

    @if ($extraction?->status !== 'completed')
        <section class="panel">
            <div class="panel-body" style="padding:28px;">
                <div class="status-icon status-warning">
                    <icon name="alert-circle" size="25" />
                </div>
                <h2 class="section-title" style="margin-top:16px;">
                    Hasil ekstraksi belum tersedia
                </h2>
                <p style="margin:8px 0 0;color:var(--muted);font-size:13px;line-height:1.8;">
                    Proses ekstraksi belum selesai atau mengalami kegagalan.
                </p>

                @if ($extraction?->error_message)
                    <div class="extraction-error">
                        {{ $extraction->error_message }}
                    </div>
                @endif
            </div>
        </section>

    @elseif (empty($data))
        <section class="panel">
            <div class="panel-body" style="padding:28px;">
                <div class="status-icon status-warning">
                    <icon name="file-question" size="25" />
                </div>
                <h2 class="section-title" style="margin-top:16px;">
                    Data ekstraksi belum tersedia
                </h2>
                <p style="margin:8px 0 0;color:var(--muted);font-size:13px;">
                    Tidak ditemukan data hasil ekstraksi pada dokumen ini.
                </p>
            </div>
        </section>

    @else
        @php
            $personalInfo = $data['personal_info'] ?? [];
        @endphp

        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('confirmed_data'))
            <div class="alert alert-danger" style="margin-bottom:20px;">
                {{ $errors->first('confirmed_data') }}
            </div>
        @endif

        {{-- Informasi pribadi --}}
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">Personal information</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Informasi Pribadi
                    </h2>
                </div>
                <span class="cv-section-count">Profil</span>
            </div>

            <div class="panel-body">
                @if (is_array($personalInfo) && collect($personalInfo)->contains(fn ($value) => is_scalar($value) && filled($value)))
                    <div class="personal-grid">
                        @foreach ($personalInfo as $label => $value)
                            @if (is_scalar($value) && filled($value))
                                <div class="personal-item">
                                    <div class="personal-label">
                                        {{ str($label)->replace('_', ' ')->title() }}
                                    </div>
                                    <div class="personal-value">{{ $value }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="empty-description">
                        Tidak ada informasi pribadi yang ditemukan.
                    </p>
                @endif

                <div class="summary-box">
                    <div class="page-eyebrow">Profile summary</div>
                    <h3 class="summary-title">Ringkasan Profil</h3>
                    <p class="summary-content">
                        {{ is_string($data['profile_summary'] ?? null) && filled($data['profile_summary']) ? $data['profile_summary'] : 'Tidak tersedia.' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Bagian hasil ekstraksi lainnya --}}
        @foreach ($sections as $key => $title)
            @php
                $items = $data[$key] ?? [];

                if (!is_array($items)) {
                    $items = [];
                }

                $items = array_filter($items, function ($item) {
                    if (is_array($item)) {
                        return count(array_filter($item, function ($value) {
                            return is_scalar($value) && filled($value);
                        })) > 0;
                    }

                    return is_scalar($item) && filled($item);
                });
            @endphp

            @if (count($items) > 0)
                <section class="panel" style="margin-bottom:16px;">
                    <div class="panel-header">
                        <div>
                            <div class="page-eyebrow">Extracted information</div>
                            <h2 class="section-title" style="margin-top:6px;">
                                {{ $title }}
                            </h2>
                        </div>
                        <span class="cv-section-count">
                            {{ count($items) }} data
                        </span>
                    </div>

                    <div class="panel-body">
                        @if (
                            in_array($key, [
                                'hard_skills',
                                'soft_skills',
                                'languages',
                                'missing_or_uncertain'
                            ])
                            && collect($items)->every(fn ($item) => is_scalar($item))
                        )
                            <div class="extracted-tags">
                                @foreach ($items as $item)
                                    <span class="extracted-tag">
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="extracted-list">
                                @foreach ($items as $item)
                                    <article class="extracted-item">
                                        @if (is_array($item))
                                            @foreach ($item as $label => $value)
                                                @if (is_scalar($value) && filled($value))
                                                    <div class="extracted-field">
                                                        <div class="extracted-label">
                                                            {{ str($label)->replace('_', ' ')->title() }}
                                                        </div>
                                                        <div class="extracted-value">
                                                            {{ $value }}
                                                        </div>
                                                    </div>
                                                @elseif (is_array($value))
                                                    @php
                                                        $subValues = collect($value)
                                                            ->filter(fn ($v) => is_scalar($v) && filled($v))
                                                            ->values();
                                                    @endphp

                                                    @if ($subValues->isNotEmpty())
                                                        <div class="extracted-field">
                                                            <div class="extracted-label">
                                                                {{ str($label)->replace('_', ' ')->title() }}
                                                            </div>
                                                            <div class="extracted-value">
                                                                {{ $subValues->join(', ') }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @elseif (is_scalar($item))
                                            <div class="extracted-value">{{ $item }}</div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        @endforeach

        {{-- Form konfirmasi data CV --}}
        <section class="panel" style="margin-top:24px;">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">Data verification</div>
                    <h2 class="section-title" style="margin-top:6px;">
                        Konfirmasi Data CV
                    </h2>
                    <p class="page-description" style="margin-top:5px;">
                        Periksa dan perbaiki data hasil ekstraksi sebelum menyimpannya.
                    </p>
                </div>

                @if ($extraction?->confirmed_at)
                    <span class="skill-badge skill-badge-success">
                        Sudah Dikonfirmasi
                    </span>
                @else
                    <span class="skill-badge skill-badge-warning">
                        Belum Dikonfirmasi
                    </span>
                @endif
            </div>

            <div class="panel-body">
                <div class="confirmation-notice">
                    <icon name="info" size="17" />
                    <p>
                        Data ditampilkan dalam format JSON agar kamu dapat memeriksa
                        dan memperbaiki informasi yang diekstrak oleh AI.
                    </p>
                </div>

                <form action="{{ route('cv.confirm', $cv) }}" method="POST">
                    @csrf

                    <div style="margin-bottom:12px;">
                        <label for="confirmed_data" class="form-label">
                            Data hasil ekstraksi (JSON)
                        </label>
                        <textarea
                            name="confirmed_data"
                            id="confirmed_data"
                            class="form-control json-editor"
                            rows="20"
                            spellcheck="false"
                            required
                        >{{ old('confirmed_data', json_encode($extraction?->confirmed_data ?? $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}</textarea>

                        <div class="json-helper">
                            Pastikan struktur JSON valid. Perhatikan tanda kurung,
                            tanda kutip, dan koma sebelum menyimpan.
                        </div>
                    </div>

                    <div class="confirmation-footer">
                        @if ($extraction?->confirmed_at)
                            <div class="confirmation-date">
                                <icon name="clock" size="14" />
                                Terakhir dikonfirmasi:
                                {{ $extraction->confirmed_at->format('d-m-Y H:i') }}
                            </div>
                        @else
                            <div class="confirmation-date">
                                Data belum dikonfirmasi.
                            </div>
                        @endif

                        <button type="submit" class="btn-primary">
                            <icon name="check-circle" size="15" />
                            Simpan Konfirmasi →
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <div class="ai-disclaimer">
            <icon name="alert-triangle" size="18" />
            <div>
                <strong>Periksa kembali hasil ekstraksi AI.</strong>
                <p>
                    Hasil ini dibuat secara otomatis. Pastikan informasi pendidikan,
                    pengalaman, dan kompetensi sesuai dengan CV asli sebelum melakukan konfirmasi.
                </p>
            </div>
        </div>
    @endif

    <style>
        .cv-back-link {
            display:inline-flex;
            align-items:center;
            gap:7px;
            text-decoration:none;
            padding:8px 12px;
            font-size:12px;
        }

        .status-icon {
            width:48px;
            height:48px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:12px;
        }

        .status-warning {
            color:#fbbf24;
            background:rgba(245,158,11,.12);
            border:1px solid rgba(245,158,11,.2);
        }

        .extraction-error {
            margin-top:18px;
            padding:14px;
            border:1px solid rgba(239,68,68,.25);
            border-radius:8px;
            background:rgba(239,68,68,.08);
            color:#fca5a5;
            font-size:12px;
            line-height:1.7;
        }

        .personal-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:18px;
        }

        .personal-item {
            min-width:0;
        }

        .personal-label,
        .extracted-label {
            margin-bottom:5px;
            color:var(--muted);
            font-size:11px;
            font-weight:600;
        }

        .personal-value,
        .extracted-value {
            overflow-wrap:anywhere;
            color:var(--ink);
            font-size:13px;
            line-height:1.7;
        }

        .summary-box {
            margin-top:24px;
            padding:18px;
            border:1px solid var(--line);
            border-radius:9px;
            background:var(--surface-raised);
        }

        .summary-title {
            margin:7px 0 9px;
            color:var(--ink);
            font-size:14px;
            font-weight:700;
        }

        .summary-content {
            margin:0;
            color:var(--muted);
            font-size:13px;
            line-height:1.8;
            white-space:pre-line;
        }

        .empty-description {
            margin:0;
            color:var(--muted);
            font-size:13px;
        }

        .cv-section-count {
            padding:5px 9px;
            border:1px solid var(--line);
            border-radius:6px;
            background:var(--surface-raised);
            color:var(--muted);
            font-size:10px;
            white-space:nowrap;
        }

        .extracted-tags {
            display:flex;
            flex-wrap:wrap;
            gap:9px;
        }

        .extracted-tag {
            padding:7px 11px;
            border:1px solid rgba(167,139,250,.2);
            border-radius:7px;
            background:rgba(124,58,237,.1);
            color:#c4b5fd;
            font-size:12px;
        }

        .extracted-list {
            display:flex;
            flex-direction:column;
            gap:12px;
        }

        .extracted-item {
            padding:15px;
            border:1px solid var(--line);
            border-radius:8px;
            background:var(--surface-raised);
        }

        .extracted-field {
            margin-bottom:12px;
        }

        .extracted-field:last-child {
            margin-bottom:0;
        }

        .confirmation-notice {
            display:flex;
            align-items:flex-start;
            gap:10px;
            margin-bottom:20px;
            padding:13px;
            border:1px solid rgba(14,165,233,.2);
            border-radius:8px;
            background:rgba(14,165,233,.07);
            color:#7dd3fc;
        }

        .confirmation-notice p {
            margin:0;
            font-size:12px;
            line-height:1.7;
        }

        .json-editor {
            min-height:360px;
            padding:16px;
            font-family:monospace;
            font-size:12px;
            line-height:1.7;
            resize:vertical;
            tab-size:4;
        }

        .json-helper {
            margin-top:8px;
            color:var(--muted);
            font-size:11px;
            line-height:1.6;
        }

        .confirmation-footer {
            display:flex;
            align-items:center;
            justify-content:space-between;
            flex-wrap:wrap;
            gap:14px;
            margin-top:20px;
        }

        .confirmation-date {
            display:flex;
            align-items:center;
            gap:6px;
            color:var(--muted);
            font-size:11px;
        }

        .ai-disclaimer {
            display:flex;
            align-items:flex-start;
            gap:12px;
            margin-top:20px;
            padding:16px;
            border:1px solid rgba(245,158,11,.2);
            border-radius:9px;
            background:rgba(245,158,11,.07);
            color:#fbbf24;
        }

        .ai-disclaimer strong {
            font-size:12px;
        }

        .ai-disclaimer p {
            margin:5px 0 0;
            color:var(--muted);
            font-size:11px;
            line-height:1.7;
        }

        @media (max-width:600px) {
            .confirmation-footer {
                align-items:stretch;
                flex-direction:column;
            }

            .confirmation-footer .btn-primary {
                width:100%;
                justify-content:center;
            }

            .json-editor {
                min-height:280px;
            }
        }
    </style>
@endsection
