@extends('layouts.app')

@section('title', 'Verifikasi Kompetensi')
@section('topbar-title', 'Verifikasi Kompetensi')

@section('page-header')
    <div style="margin-bottom:28px;">
        <div class="page-eyebrow">Administration / Competency / Review</div>
        <h1 class="page-title">Verifikasi Kompetensi.</h1>
        <p class="page-description">
            Periksa dan validasi tingkat kompetensi yang diajukan mahasiswa.
        </p>
    </div>
@endsection

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <div class="page-eyebrow">Verification workspace</div>
            <div style="font-size:14px;color:var(--muted);margin-top:5px;">
                Pemeriksaan kompetensi mahasiswa yang masih menunggu keputusan.
            </div>
        </div>

        <a href="{{ route('admin.skills.verification.history') }}"
           class="btn-secondary">
            Riwayat Verifikasi →
        </a>
    </div>

    <section class="panel">
        <div class="panel-header">
            <div>
                <div class="page-eyebrow">Pending submissions</div>
                <h2 class="section-title" style="margin-top:6px;">
                    Pengajuan Kompetensi
                </h2>
                <p class="page-description" style="margin-top:5px;">
                    Periksa informasi pengajuan sebelum menyetujui atau menolaknya.
                </p>
            </div>

            <span class="badge badge-warning">
                {{ $pendingSkills->total() }} Menunggu
            </span>
        </div>

        <div class="panel-body">
            @if ($pendingSkills->isEmpty())
                <div style="padding:42px 20px;text-align:center;">
                    <div style="font-size:32px;margin-bottom:12px;">✓</div>
                    <h3 style="font-size:17px;font-weight:700;margin-bottom:7px;">
                        Tidak Ada Pengajuan Tertunda
                    </h3>
                    <p class="page-description" style="margin:0 auto;max-width:420px;">
                        Semua pengajuan kompetensi sudah diproses atau belum ada pengajuan baru.
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
                                <th>Status</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pendingSkills as $item)
                                <tr>
                                    <td>
                                        {{ $pendingSkills->firstItem() + $loop->index }}
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
                                            <span class="badge badge-danger">
                                                Belum diisi
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-warning">
                                            Menunggu Verifikasi
                                        </span>
                                    </td>

                                    <td style="white-space:nowrap;">
                                        {{ $item->created_at?->format('d-m-Y H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        <div style="display:flex;gap:7px;flex-wrap:wrap;">
                                            <form
                                                action="{{ route('admin.skills.verification.approve', $item) }}"
                                                method="POST"
                                                onsubmit="return confirm('Setujui kompetensi ini?')"
                                            >
                                                @csrf
                                                <button type="submit"
                                                        class="btn-primary"
                                                        style="padding:8px 11px;font-size:11px;"
                                                        @disabled(
                                                            !is_numeric($item->actual_level) ||
                                                            (int) $item->actual_level < 1 ||
                                                            (int) $item->actual_level > 5 ||
                                                            (float) $item->actual_level !== (float) (int) $item->actual_level
                                                        )>
                                                    Setujui
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('admin.skills.verification.reject', $item) }}"
                                                method="POST"
                                                onsubmit="return confirm('Tolak kompetensi ini?')"
                                            >
                                                @csrf
                                                <button type="submit"
                                                        class="btn-danger"
                                                        style="padding:8px 11px;font-size:11px;">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:22px;">
                    {{ $pendingSkills->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection