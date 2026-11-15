```php
@extends('layouts.app')

@section('title', 'Detail Lowongan')
@section('topbar-title', 'Detail Lowongan')

@section('page-header')
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:20px;flex-wrap:wrap;margin-bottom:28px;">
        <div>
            <div class="page-eyebrow">Administration / Job detail</div>
            <h1 class="page-title">{{ $jobPosting->title }}</h1>
            <p class="page-description">
                {{ $jobPosting->company ?? 'Perusahaan belum diisi' }}
                ·
                {{ $jobPosting->location ?? 'Lokasi belum diisi' }}
            </p>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('admin.job-postings.edit', $jobPosting) }}" class="btn-primary">
                Edit Lowongan
            </a>
            <a href="{{ route('admin.job-postings.index') }}" class="btn-secondary">
                Kembali
            </a>
        </div>
    </div>
@endsection

@section('content')

{{-- Status Lowongan --}}
<div class="panel" style="padding:20px 24px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
            <div class="page-eyebrow">Publication status</div>
            <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
                @if($jobPosting->is_active)
                    <span class="badge badge-success">Aktif</span>
                    <span style="font-size:13px;color:var(--muted);">
                        Lowongan tersedia untuk analisis mahasiswa.
                    </span>
                @else
                    <span class="badge badge-neutral">Nonaktif</span>
                    <span style="font-size:13px;color:var(--muted);">
                        Lowongan tidak tersedia untuk pencocokan baru.
                    </span>
                @endif
            </div>
        </div>

        <form action="{{ route('admin.job-postings.status', $jobPosting) }}"
              method="POST"
              onsubmit="return confirm('Yakin ingin mengubah status lowongan ini?')">
            @csrf
            @method('PATCH')

            @if($jobPosting->is_active)
                <button type="submit" class="btn-danger">
                    Nonaktifkan Lowongan
                </button>
            @else
                <button type="submit" class="btn-primary">
                    Aktifkan Kembali
                </button>
            @endif
        </form>
    </div>
</div>

{{-- Informasi Lowongan --}}
<section class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
        <div>
            <div class="page-eyebrow">Job information</div>
            <h2 class="section-title" style="margin-top:6px;">Informasi Lowongan</h2>
        </div>
    </div>

    <div class="panel-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:22px;margin-bottom:24px;">
            <div>
                <div class="form-label">Perusahaan</div>
                <div>{{ $jobPosting->company ?: '-' }}</div>
            </div>

            <div>
                <div class="form-label">Lokasi</div>
                <div>{{ $jobPosting->location ?: '-' }}</div>
            </div>

            <div>
                <div class="form-label">Pendidikan</div>
                <div>{{ $jobPosting->education_requirement ?: '-' }}</div>
            </div>

            <div>
                <div class="form-label">Pengalaman</div>
                <div>{{ $jobPosting->experience_requirement ?: '-' }}</div>
            </div>
        </div>

        <div style="border-top:1px solid var(--line);padding-top:22px;">
            <div class="form-label">Deskripsi Pekerjaan</div>
            <div style="font-size:14px;line-height:1.8;white-space:pre-line;">
                {{ $jobPosting->description ?: '-' }}
            </div>
        </div>

        <div style="border-top:1px solid var(--line);margin-top:22px;padding-top:22px;">
            <div class="page-eyebrow">Source & metadata</div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-top:16px;">
                <div>
                    <div class="form-label">Sumber Lowongan</div>
                    <div>{{ $jobPosting->source_name ?: '-' }}</div>
                </div>

                <div>
                    <div class="form-label">Terakhir Diperbarui</div>
                    <div>
                        {{ $jobPosting->updated_at ? $jobPosting->updated_at->format('d M Y, H:i') : '-' }}
                    </div>
                </div>
            </div>

            <div style="margin-top:18px;">
                <div class="form-label">URL Sumber</div>
                @if($jobPosting->source_url)
                    <a href="{{ $jobPosting->source_url }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       style="color:var(--forest);font-size:13px;overflow-wrap:anywhere;">
                        {{ $jobPosting->source_url }}
                    </a>
                @else
                    <span style="color:var(--muted);">-</span>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Persyaratan Kompetensi --}}
<section class="panel">
    <div class="panel-header">
        <div>
            <div class="page-eyebrow">Competency requirements</div>
            <h2 class="section-title" style="margin-top:6px;">Persyaratan Kompetensi</h2>
            <p class="page-description" style="margin-top:6px;">
                Kelola kompetensi, level target, dan jenis faktor untuk perhitungan Profile Matching.
            </p>
        </div>
    </div>

    <div class="panel-body">

        {{-- Form Tambah Kompetensi --}}
        <div style="padding:20px;background:var(--paper);border:1px solid var(--line);border-radius:8px;margin-bottom:28px;">
            <div class="page-eyebrow">New requirement</div>
            <h3 class="section-title" style="margin-top:6px;margin-bottom:20px;">
                Tambah Kompetensi
            </h3>

            <form action="{{ route('admin.job-postings.skills.store', $jobPosting) }}"
                  method="POST">
                @csrf

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;">
                    <div>
                        <label for="skill_id" class="form-label">Kompetensi</label>
                        <select name="skill_id" id="skill_id" class="form-control" required>
                            <option value="">Pilih kompetensi</option>
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}" @selected(old('skill_id') == $skill->id)>
                                    {{ $skill->name }}{{ $skill->category ? ' — '.$skill->category : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('skill_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="required_level" class="form-label">Level Target (1–5)</label>
                        <select name="required_level" id="required_level" class="form-control" required>
                            <option value="">Pilih level</option>
                            @for($level = 1; $level <= 5; $level++)
                                <option value="{{ $level }}" @selected(old('required_level') == $level)>
                                    Level {{ $level }}
                                </option>
                            @endfor
                        </select>
                        @error('required_level')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="is_core_factor" class="form-label">Jenis Faktor</label>
                        <select name="is_core_factor" id="is_core_factor" class="form-control" required>
                            <option value="">Pilih jenis faktor</option>
                            <option value="1" @selected(old('is_core_factor') === '1')>
                                Core Factor
                            </option>
                            <option value="0" @selected(old('is_core_factor') === '0')>
                                Secondary Factor
                            </option>
                        </select>
                        @error('is_core_factor')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <label for="notes" class="form-label">Catatan (opsional)</label>
                    <textarea name="notes"
                              id="notes"
                              class="form-control"
                              rows="2">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                @if($errors->has('skill_id') || $errors->has('required_level') || $errors->has('is_core_factor') || $errors->has('notes'))
                    <div class="alert alert-danger" style="margin-top:16px;">
                        Periksa kembali data kompetensi yang kamu masukkan.
                    </div>
                @endif

                <div style="margin-top:18px;">
                    <button type="submit" class="btn-primary">
                        + Tambah Kompetensi
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar Kompetensi --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                <h3 class="section-title">Daftar Persyaratan</h3>
                <span class="page-description" style="margin:0;">
                    {{ $jobPosting->jobSkills->count() }} kompetensi
                </span>
            </div>

            @if($jobPosting->jobSkills->isEmpty())
                <div class="alert" style="text-align:center;padding:28px;">
                    Belum ada kompetensi yang ditambahkan ke lowongan ini.
                </div>
            @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kompetensi</th>
                                <th>Kategori</th>
                                <th>Level</th>
                                <th>Jenis Faktor</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobPosting->jobSkills as $jobSkill)
                                <tr>
                                    <td style="font-weight:600;">
                                        {{ $jobSkill->skill->name }}
                                    </td>
                                    <td>{{ $jobSkill->skill->category ?? '-' }}</td>
                                    <td>{{ $jobSkill->required_level ?? '-' }}</td>
                                    <td>
                                        @if($jobSkill->is_core_factor)
                                            <span class="badge badge-success">Core Factor</span>
                                        @else
                                            <span class="badge badge-neutral">Secondary Factor</span>
                                        @endif
                                    </td>
                                    <td>{{ $jobSkill->notes ?? '-' }}</td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                            <a href="#edit-job-skill-{{ $jobSkill->id }}"
                                               class="btn-secondary"
                                               style="font-size:12px;min-height:34px;padding:6px 12px;">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('admin.job-postings.skills.destroy', [$jobPosting, $jobSkill]) }}"
                                                  onsubmit="return confirm('Yakin ingin menghapus persyaratan kompetensi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn-danger"
                                                        style="font-size:12px;min-height:34px;padding:6px 12px;">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="edit-job-skill-{{ $jobSkill->id }}">
                                    <td colspan="6" style="padding:0;">
                                        <details style="padding:14px 18px;background:var(--paper);">
                                            <summary style="cursor:pointer;color:var(--forest);font-size:13px;font-weight:700;">
                                                Edit persyaratan: {{ $jobSkill->skill->name }}
                                            </summary>

                                            <form method="POST"
                                                  action="{{ route('admin.job-postings.skills.update', [$jobPosting, $jobSkill]) }}"
                                                  style="margin-top:18px;">
                                                @csrf
                                                @method('PUT')

                                                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                                                    <div>
                                                        <label class="form-label">Level yang Dibutuhkan</label>
                                                        <select name="required_level" class="form-control" required>
                                                            @for($level = 1; $level <= 5; $level++)
                                                                <option value="{{ $level }}" @selected($jobSkill->required_level == $level)>
                                                                    Level {{ $level }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="form-label">Jenis Faktor</label>
                                                        <select name="is_core_factor" class="form-control" required>
                                                            <option value="1" @selected($jobSkill->is_core_factor)>
                                                                Core Factor
                                                            </option>
                                                            <option value="0" @selected(!$jobSkill->is_core_factor)>
                                                                Secondary Factor
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="form-label">Catatan</label>
                                                        <input type="text"
                                                               name="notes"
                                                               class="form-control"
                                                               value="{{ $jobSkill->notes }}"
                                                               placeholder="Catatan tambahan">
                                                    </div>
                                                </div>

                                                <div style="margin-top:16px;">
                                                    <button type="submit" class="btn-primary">
                                                        Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection
```