
@extends('layouts.app')

@section('title', 'Kompetensi Mahasiswa')
@section('topbar-title', 'Kompetensi Mahasiswa')

@section('page-header')
    <div style="margin-bottom:28px;">
        <div class="page-eyebrow">Career workspace / Skills</div>
        <h1 class="page-title">Kompetensi Mahasiswa.</h1>
        <p class="page-description">
            Kelola tingkat kemampuanmu sebagai dasar analisis kesesuaian kompetensi dengan kebutuhan pekerjaan.
        </p>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <strong>Periksa kembali data kompetensimu.</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:minmax(0, 1fr) 280px;gap:20px;align-items:start;">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <div class="page-eyebrow">Skill assessment</div>
                    <h2 class="section-title" style="margin-top:6px;">Daftar Kompetensi</h2>
                    <p class="page-description" style="margin-top:5px;">
                        Pilih tingkat kemampuan yang paling sesuai. Kamu tidak harus mengisi semua kompetensi sekaligus.
                    </p>
                </div>
            </div>

            <div class="panel-body">
                <form action="{{ route('skills.store') }}" method="POST">
                    @csrf

                    @php
                        $groupedSkills = $skills->groupBy('category');
                    @endphp

                    @forelse ($groupedSkills as $category => $categorySkills)
                        <div style="margin-bottom:26px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                                <span style="width:4px;height:22px;background:var(--purple-light);border-radius:4px;"></span>
                                <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--ink);">
                                    {{ $category ?: 'Lainnya' }}
                                </h3>
                                <span style="font-size:11px;color:var(--muted);">
                                    {{ $categorySkills->count() }} kompetensi
                                </span>
                            </div>

                            <div style="border:1px solid var(--line);border-radius:10px;overflow:hidden;">
                                @foreach ($categorySkills as $skill)
                                    @php
                                        $savedSkill = $studentSkills->get($skill->id);
                                        $oldLevel = old('skills.' . $skill->id . '.actual_level');
                                        $selectedLevel = $oldLevel !== null
                                            ? $oldLevel
                                            : $savedSkill?->actual_level;
                                    @endphp

                                    <div class="skill-row" style="display:grid;grid-template-columns:minmax(0, 1fr) minmax(200px, 280px);gap:18px;align-items:center;padding:16px;border-bottom:1px solid var(--line);">
                                        <div>
                                            <label
                                                for="skill_{{ $skill->id }}"
                                                style="display:block;margin:0 0 7px;font-size:13px;font-weight:650;color:var(--ink);"
                                            >
                                                {{ $skill->name }}
                                            </label>

                                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                                @if ($cvSkillIds->contains($skill->id))
                                                    <span class="skill-badge skill-badge-info">
                                                        <span>CV</span> Terdeteksi dari CV
                                                    </span>
                                                @endif

                                                @if ($savedSkill)
                                                    @if ($savedSkill->confirmation_status === 'pending')
                                                        <span class="skill-badge skill-badge-warning">Menunggu Verifikasi</span>
                                                    @elseif ($savedSkill->confirmation_status === 'approved')
                                                        <span class="skill-badge skill-badge-success">Disetujui</span>
                                                    @elseif ($savedSkill->confirmation_status === 'rejected')
                                                        <span class="skill-badge skill-badge-danger">Ditolak</span>
                                                    @endif
                                                @else
                                                    <span class="skill-badge skill-badge-neutral">Belum Diajukan</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <select
                                                id="skill_{{ $skill->id }}"
                                                name="skills[{{ $skill->id }}][actual_level]"
                                                class="form-control"
                                                style="width:100%;"
                                            >
                                                <option value="">Belum dinilai / pilih level</option>

                                                @foreach ([
                                                    1 => '1 - Pemula',
                                                    2 => '2 - Dasar',
                                                    3 => '3 - Menengah',
                                                    4 => '4 - Mahir',
                                                    5 => '5 - Sangat Mahir',
                                                ] as $level => $label)
                                                    <option
                                                        value="{{ $level }}"
                                                        @selected((string) $selectedLevel === (string) $level)
                                                    >
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input
                                                type="hidden"
                                                name="skills[{{ $skill->id }}][skill_id]"
                                                value="{{ $skill->id }}"
                                            >
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="padding:36px 20px;text-align:center;">
                            <div style="font-size:28px;margin-bottom:10px;">◇</div>
                            <h3 style="font-size:15px;color:var(--ink);">Belum ada kompetensi</h3>
                            <p style="margin:0;color:var(--muted);font-size:13px;">
                                Daftar kompetensi belum tersedia saat ini.
                            </p>
                        </div>
                    @endforelse

                    @if ($groupedSkills->isNotEmpty())
                        <div style="display:flex;justify-content:flex-end;margin-top:24px;">
                            <button type="submit" class="btn-primary">
                                Simpan Kompetensi →
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </section>

        <aside style="display:flex;flex-direction:column;gap:16px;">
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <div class="page-eyebrow">Level guide</div>
                        <h2 class="section-title" style="margin-top:6px;">Panduan Kemampuan</h2>
                    </div>
                </div>

                <div class="panel-body">
                    @php
                        $levelGuides = [
                            1 => 'Pemula',
                            2 => 'Memahami dasar',
                            3 => 'Mampu mengerjakan tugas sederhana',
                            4 => 'Mampu mengerjakan tugas secara mandiri',
                            5 => 'Mahir menangani tugas kompleks',
                        ];
                    @endphp

                    <div style="display:flex;flex-direction:column;gap:16px;">
                        @foreach ($levelGuides as $level => $description)
                            <div style="display:flex;align-items:flex-start;gap:12px;">
                                <div style="flex-shrink:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:1px solid var(--line);border-radius:7px;background:var(--surface-raised);color:var(--purple-light);font-size:12px;font-weight:750;">
                                    {{ $level }}
                                </div>
                                <div style="padding-top:3px;color:var(--muted);font-size:12px;line-height:1.6;">
                                    {{ $description }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-body">
                    <div class="page-eyebrow">Important note</div>
                    <h3 style="margin:8px 0;font-size:14px;color:var(--ink);">
                        Isi sesuai kemampuan sebenarnya
                    </h3>
                    <p style="margin:0;color:var(--muted);font-size:12px;line-height:1.8;">
                        Pilih level yang paling menggambarkan kemampuanmu saat ini. Data kompetensi akan digunakan dalam proses analisis kesesuaian terhadap lowongan kerja.
                    </p>
                </div>
            </section>
        </aside>
    </div>

    <style>
        .skill-badge {
            display:inline-flex;
            align-items:center;
            gap:4px;
            padding:4px 8px;
            border-radius:5px;
            font-size:10px;
            font-weight:650;
            line-height:1.4;
        }

        .skill-badge-info {
            color:#7dd3fc;
            background:rgba(14,165,233,.12);
            border:1px solid rgba(14,165,233,.22);
        }

        .skill-badge-warning {
            color:#fcd34d;
            background:rgba(245,158,11,.12);
            border:1px solid rgba(245,158,11,.22);
        }

        .skill-badge-success {
            color:#86efac;
            background:rgba(34,197,94,.12);
            border:1px solid rgba(34,197,94,.22);
        }

        .skill-badge-danger {
            color:#fca5a5;
            background:rgba(239,68,68,.12);
            border:1px solid rgba(239,68,68,.22);
        }

        .skill-badge-neutral {
            color:var(--muted);
            background:var(--surface-raised);
            border:1px solid var(--line);
        }

        @media (max-width: 900px) {
            .skill-row {
                grid-template-columns:1fr !important;
                gap:12px !important;
            }
        }

        @media (max-width: 1050px) {
            .skills-page-layout {
                grid-template-columns:1fr !important;
            }
        }
    </style>
@endsection
