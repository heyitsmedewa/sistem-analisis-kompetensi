@extends('layouts.app')

@section('title', 'Riwayat Verifikasi')
@section('topbar-title', 'Riwayat Verifikasi')

@section('page-header')
    <div style="margin-bottom:28px;">
        <div class="page-eyebrow">Administration / Competency / History</div>
        <h1 class="page-title">Riwayat Verifikasi.</h1>
        <p class="page-description">
            Pantau seluruh kompetensi mahasiswa yang telah selesai diperiksa.
        </p>
    </div>
@endsection

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <div class="page-eyebrow">Verification records</div>
            <div style="font-size:14px;color:var(--muted);margin-top:5px;">
                Catatan keputusan persetujuan dan penolakan kompetensi.
            </div>
        </div>

        <a href="{{ route('admin.skills.verification.index') }}"
           class="btn-secondary">
            ← Kembali ke Verifikasi
        </a>
    </div>

    <section class="panel">
        <div class="panel-header">
            <div>
                <div class="page-eyebrow">Completed submissions</div>
                <h2 class="section-title" style="margin-top:6px;">
                    Catatan Verifikasi
                </h2>
                <p class="page-description" style="margin-top:5px;">
                    Daftar kompetensi yang telah disetujui atau ditolak admin.
                </p>
            </div>

            <span class="badge badge-neutral">
                {{ $verifiedSkills->total() }} Data
            </span>
        </div>

        <div class="panel-body">
            @if ($verifiedSkills->isEmpty())
                <div style="padding:42px 20px;text-align:center;">
                    <div style="font-size:32px;margin-bottom:12px;">◷</div>

                    <h3 style="font-size:17px;font-weight:700;margin-bottom:7px;">
                        Belum Ada Riwayat
                    </h3>

                    <p class="page-description" style="margin:0 auto;max-width:420px;">
                        Kompetensi mahasiswa yang telah selesai diverifikasi akan muncul di halaman ini.
                    </p>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Mahasiswa</th>
                                <th>Kompetensi</th>
                                <th>Kategori</th>
                                <th>Level</th>
                                <th>Keputusan</th>
                                <th>Tanggal Pembaruan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($verifiedSkills as $item)
                                <tr>
                                    <td>
                                        {{ $verifiedSkills->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        <div style="font-weight:700;color:var(--ink);">
                                            {{ $item->studentProfile?->user?->name ?? 'Tidak tersedia' }}
                                        </div>
                                        <div style="font-size:11px;color:var(--muted);margin-top:3px;">
                                            Mahasiswa
                                        </div>
                                    </td>

                                    <td>
                                        <div style="font-weight:600;color:var(--ink);">
                                            {{ $item->skill?->name ?? 'Tidak tersedia' }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $item->skill?->category ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($item->actual_level !== null)
                                            <span style="font-weight:700;color:var(--forest-700);">
                                                {{ $item->actual_level }} / 5
                                            </span>
                                        @else
                                            <span class="badge badge-neutral">
                                                Belum diisi
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($item->confirmation_status === 'approved')
                                            <span class="badge badge-success">
                                                Disetujui
                                            </span>
                                        @elseif ($item->confirmation_status === 'rejected')
                                            <span class="badge badge-danger">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>

                                    <td style="white-space:nowrap;">
                                        {{ $item->updated_at?->format('d-m-Y H:i') ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:22px;">
                    {{ $verifiedSkills->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection