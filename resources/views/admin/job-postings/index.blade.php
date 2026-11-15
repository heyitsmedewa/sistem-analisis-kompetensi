@extends('layouts.app')

@section('title', 'Manajemen Lowongan')
@section('topbar-title', 'Manajemen Lowongan')

@section('page-header')
<div style="display:flex;justify-content:space-between;align-items:end;gap:16px;flex-wrap:wrap;margin-bottom:28px;">
    <div>
        <div class="page-eyebrow">Administration / Jobs</div>
        <h1 class="page-title">Manajemen Lowongan.</h1>
        <p class="page-description">
            Kelola lowongan kerja, persyaratan kompetensi, dan status aktif.
        </p>
    </div>

    <a href="{{ route('admin.job-postings.create') }}" class="btn-primary">
        + Tambah Lowongan
    </a>
</div>
@endsection

@section('content')

@if($jobPostings->isEmpty())

    <div class="panel">
        <div class="panel-body" style="text-align:center;padding:48px 24px;">
            <div class="page-eyebrow">No data</div>
            <h2 class="section-title" style="margin-top:10px;">Belum ada lowongan.</h2>
            <p class="page-description">
                Tambahkan lowongan pertama agar mahasiswa dapat melakukan analisis kecocokan.
            </p>

            <div style="margin-top:24px;">
                <a href="{{ route('admin.job-postings.create') }}" class="btn-primary">
                    Tambah Lowongan
                </a>
            </div>
        </div>
    </div>

@else

    <div class="panel">
        <div class="panel-header">
            <div>
                <div class="page-eyebrow">Job database</div>
                <h2 class="section-title" style="margin-top:6px;">
                    Daftar Lowongan
                </h2>
            </div>

            <span class="page-description" style="margin:0;">
                {{ $jobPostings->total() }} lowongan
            </span>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Lowongan</th>
                        <th>Perusahaan</th>
                        <th>Lokasi</th>
                        <th>Kompetensi</th>
                        <th>Pencocokan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($jobPostings as $job)
                    <tr>
                        <td>{{ $jobPostings->firstItem() + $loop->index }}</td>

                        <td>
                            <div style="font-weight:600;">
                                {{ $job->title }}
                            </div>
                        </td>

                        <td>{{ $job->company ?? '-' }}</td>

                        <td>{{ $job->location ?? '-' }}</td>

                        <td>{{ $job->job_skills_count }}</td>

                        <td>{{ $job->matching_results_count }}</td>

                        <td>
                            @if($job->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-neutral">Nonaktif</span>
                            @endif
                        </td>

                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <a href="{{ route('admin.job-postings.show',$job) }}"
                                   class="btn-secondary"
                                   style="min-height:34px;padding:6px 12px;font-size:12px;">
                                    Detail
                                </a>

                                <a href="{{ route('admin.job-postings.edit',$job) }}"
                                   class="btn-secondary"
                                   style="min-height:34px;padding:6px 12px;font-size:12px;">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:20px 24px;border-top:1px solid var(--line);">
            {{ $jobPostings->links() }}
        </div>
    </div>

@endif

@endsection