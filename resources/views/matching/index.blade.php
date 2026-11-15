
@extends('layouts.app')

@section('title', 'Analisis Kecocokan Lowongan')

@section('content')
@php
    $inactiveResults = $matchingResults->filter(
        fn ($result) => $result->jobPosting && ! $result->jobPosting->is_active
    );
@endphp

<div class="space-y-6">

    {{-- Page Header --}}
    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-purple-400">
                Analisis Kompetensi
            </p>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Analisis Kecocokan Lowongan
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                Bandingkan kompetensi terverifikasi dengan persyaratan lowongan
                menggunakan metode Profile Matching.
            </p>
        </div>

        <form action="{{ route('matching.calculate-all') }}" method="POST">
            @csrf
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-500 sm:w-auto"
            >
                Analisis Semua Lowongan Aktif
            </button>
        </form>
    </section>

    {{-- Notifications --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
            <p class="font-semibold">Analisis belum dapat dilakukan:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Method Information --}}
    <section class="rounded-2xl border border-purple-500/20 bg-purple-500/[0.06] p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-500/15 text-lg font-bold text-purple-300">
                PM
            </div>
            <div>
                <h2 class="font-semibold text-white">Metode Profile Matching</h2>
                <p class="mt-1 text-sm leading-6 text-slate-400">
                    Penilaian menggunakan Core Factor sebesar
                    <span class="font-semibold text-purple-300">60%</span>
                    dan Secondary Factor sebesar
                    <span class="font-semibold text-purple-300">40%</span>.
                    Hasil dihitung berdasarkan bobot gap kompetensi yang telah ditentukan.
                </p>
            </div>
        </div>
    </section>

   
{{-- Ranking --}}
<section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
    <div class="border-b border-slate-800 p-5">
        <h2 class="text-lg font-semibold text-white">
            Peringkat Kecocokan Lowongan
        </h2>
        <p class="mt-1 text-sm text-slate-400">
            Hasil pemeringkatan berdasarkan tingkat kelengkapan evaluasi kompetensi.
        </p>
    </div>

    @if ($finalRankingResults->isEmpty() && $provisionalRankingResults->isEmpty())
        <div class="px-5 py-12 text-center">
            <p class="font-semibold text-white">Belum ada hasil pemeringkatan</p>
            <p class="mt-2 text-sm text-slate-400">
                Analisis lowongan aktif untuk mulai melihat peringkat kecocokan.
            </p>
        </div>
    @else
        {{-- Final Ranking --}}
        <div class="p-5">
            <div class="mb-4">
                <h3 class="font-semibold text-emerald-300">
                    Peringkat Final
                </h3>
                <p class="mt-1 text-xs text-slate-400">
                    Hasil dengan seluruh persyaratan kompetensi telah dievaluasi.
                </p>
            </div>

            @if ($finalRankingResults->isEmpty())
                <p class="rounded-xl border border-slate-800 bg-slate-950/30 p-4 text-sm text-slate-500">
                    Belum ada hasil dengan status final.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[550px] text-left text-sm">
                        <thead class="bg-slate-950/50 text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Peringkat</th>
                                <th class="px-4 py-3 font-medium">Lowongan</th>
                                <th class="px-4 py-3 font-medium">Skor</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @php
                                $previousScore = null;
                                $currentRank = 0;
                            @endphp

                            @foreach ($finalRankingResults as $index => $ranking)
                                @php
                                    if (
                                        $previousScore === null ||
                                        (float) $ranking->final_score !== (float) $previousScore
                                    ) {
                                        $currentRank = $index + 1;
                                    }

                                    $previousScore = $ranking->final_score;
                                @endphp

                                <tr class="transition hover:bg-slate-800/40">
                                    <td class="px-4 py-4">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 font-semibold text-emerald-300">
                                            {{ $currentRank }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-white">
                                            {{ $ranking->jobPosting->title }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $ranking->jobPosting->company ?? 'Perusahaan tidak dicantumkan' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="font-semibold tabular-nums text-white">
                                            {{ number_format((float) $ranking->final_score, 2) }}
                                        </span>
                                        <span class="text-slate-500">/ 5,00</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                                            Final
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Provisional Ranking --}}
        <div class="border-t border-slate-800 p-5">
            <div class="mb-4">
                <h3 class="font-semibold text-amber-300">
                    Peringkat Sementara
                </h3>
                <p class="mt-1 text-xs text-slate-400">
                    Hasil dengan sebagian persyaratan kompetensi belum terverifikasi. Urutan dapat berubah setelah verifikasi.
                </p>
            </div>

            @if ($provisionalRankingResults->isEmpty())
                <p class="rounded-xl border border-slate-800 bg-slate-950/30 p-4 text-sm text-slate-500">
                    Belum ada hasil dengan skor sementara.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[550px] text-left text-sm">
                        <thead class="bg-slate-950/50 text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Peringkat</th>
                                <th class="px-4 py-3 font-medium">Lowongan</th>
                                <th class="px-4 py-3 font-medium">Skor Sementara</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @php
                                $previousScore = null;
                                $currentRank = 0;
                            @endphp

                            @foreach ($provisionalRankingResults as $index => $ranking)
                                @php
                                    if (
                                        $previousScore === null ||
                                        (float) $ranking->final_score !== (float) $previousScore
                                    ) {
                                        $currentRank = $index + 1;
                                    }

                                    $previousScore = $ranking->final_score;
                                @endphp

                                <tr class="transition hover:bg-slate-800/40">
                                    <td class="px-4 py-4">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 font-semibold text-amber-300">
                                            {{ $currentRank }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-white">
                                            {{ $ranking->jobPosting->title }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $ranking->jobPosting->company ?? 'Perusahaan tidak dicantumkan' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="font-semibold tabular-nums text-white">
                                            {{ number_format((float) $ranking->final_score, 2) }}
                                        </span>
                                        <span class="text-slate-500">/ 5,00</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">
                                            Provisional
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</section>

    {{-- Active Job Postings --}}
    <section>
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Daftar Lowongan Aktif</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Detail hasil pencocokan kompetensi pada setiap lowongan.
                </p>
            </div>
            <span class="text-xs text-slate-500">
                {{ $jobPostings->total() }} lowongan tersedia
            </span>
        </div>

        @if ($jobPostings->isEmpty())
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 px-5 py-12 text-center">
                <h3 class="font-semibold text-white">Belum ada lowongan aktif</h3>
                <p class="mt-2 text-sm text-slate-400">
                    Lowongan kerja akan muncul setelah tersedia.
                </p>
            </div>
        @else
            <div class="space-y-5">
                @foreach ($jobPostings as $job)
                    @php
                        $result = $resultsByJob->get($job->id);
                        $hasVisibleResult = $result && in_array(
                            $result->status,
                            ['provisional', 'final', 'calculated'],
                            true
                        );
                    @endphp

                    <article class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">

                        {{-- Job Information --}}
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col gap-5 xl:flex-row xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-white">
                                            {{ $job->title }}
                                        </h3>
                                        <span class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-300">
                                            Aktif
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm text-slate-400">
                                        {{ $job->company ?: 'Perusahaan belum dicantumkan' }}
                                        @if ($job->location)
                                            <span class="mx-1 text-slate-600">·</span>
                                            {{ $job->location }}
                                        @endif
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="rounded-lg border border-slate-700 bg-slate-800/70 px-3 py-1.5 text-xs text-slate-300">
                                            {{ $job->job_skills_count }} persyaratan kompetensi
                                        </span>
                                    </div>

                                    {{-- Source --}}
                                    <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                                        @if ($job->source_name)
                                            <div>
                                                <span class="text-slate-400">Sumber:</span>
                                                @if ($job->source_url)
                                                    <a
                                                        href="{{ $job->source_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="ml-1 text-purple-400 hover:text-purple-300 hover:underline"
                                                    >
                                                        {{ $job->source_name }}
                                                    </a>
                                                @else
                                                    <span class="ml-1 text-slate-400">{{ $job->source_name }}</span>
                                                @endif
                                            </div>
                                        @elseif ($job->source_url)
                                            <div>
                                                <span class="text-slate-400">Sumber:</span>
                                                <a
                                                    href="{{ $job->source_url }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="ml-1 text-purple-400 hover:underline"
                                                >
                                                    Lihat sumber lowongan
                                                </a>
                                            </div>
                                        @endif

                                        @if ($job->collected_at)
                                            <div>
                                                Tanggal dikumpulkan:
                                                {{ $job->collected_at->format('d M Y, H:i') }}
                                            </div>
                                        @endif

                                        @if ($job->updated_at)
                                            <div>
                                                Terakhir diperbarui:
                                                {{ $job->updated_at->format('d M Y, H:i') }}
                                            </div>
                                        @endif
                                    </div>

                                    @if ($job->description)
                                        <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-400">
                                            {{ \Illuminate\Support\Str::limit($job->description, 250) }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Score and Actions --}}
                                <div class="flex shrink-0 flex-col gap-4 xl:w-56">
                                    <div class="xl:text-right">
                                        @if ($hasVisibleResult)
                                            @if ($result->status === 'provisional')
                                                <span class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">
                                                    Skor sementara
                                                </span>
                                            @elseif ($result->status === 'final')
                                                <span class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                                                    Skor final
                                                </span>
                                            @else
                                                <span class="rounded-lg border border-slate-600 bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-400">
                                                    Hasil lama - hitung ulang
                                                </span>
                                            @endif

                                            <p class="mt-3 text-xs text-slate-500">
    Skor Profile Matching
</p>

@if ($result->final_score !== null)
    <p class="mt-1 text-3xl font-bold tabular-nums text-white">
        {{ number_format((float) $result->final_score, 2) }}
        <span class="text-base font-normal text-slate-500">/ 5</span>
    </p>

    <p class="mt-1 text-xs text-slate-500">
        {{ number_format(((float) $result->final_score / 5) * 100, 1) }}%
        dari skor maksimum
    </p>
@else
    <p class="mt-1 text-lg font-semibold text-amber-300">
        Skor belum tersedia
    </p>

    <p class="mt-1 text-xs text-slate-500">
        Data kompetensi terverifikasi belum mencukupi
        untuk menghitung kedua faktor.
    </p>
@endif
                                        @else
                                            <span class="rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-400">
                                                Belum dianalisis
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        @if ($hasVisibleResult)
                                            <form method="POST" action="{{ route('matching.calculate', $job) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="w-full rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-purple-500"
                                                >
                                                    Hitung Ulang
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('matching.calculate', $job) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="w-full rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-purple-500 disabled:cursor-not-allowed disabled:opacity-40"
                                                    @disabled($job->job_skills_count < 2)
                                                >
                                                    Analisis Kecocokan
                                                </button>
                                            </form>
                                        @endif

                                        @if ($job->job_skills_count < 2)
                                            <p class="text-center text-xs text-red-400">
                                                Persyaratan kompetensi belum mencukupi.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Matching Details --}}
                        @if ($hasVisibleResult)
                            <div class="border-t border-slate-800 bg-slate-950/20 p-5 sm:p-6">
                                @php
                                    $details = $result->details;
                                    $evaluatedCount = $details
                                        ->where('evaluation_status', 'evaluated')
                                        ->count();
                                    $unverifiedCount = $details->count() - $evaluatedCount;
                                    $coverage = $details->count() > 0
    ? ($evaluatedCount / $details->count()) * 100
    : 0;
                                  $latestRecommendation = $result->aiRecommendations->first();

$recommendationContent = null;

if (
    $latestRecommendation
    && $latestRecommendation->status === 'completed'
) {
    $decodedContent = json_decode(
        $latestRecommendation->content,
        true
    );

    if (
        is_array($decodedContent)
        && isset($decodedContent['summary'])
        && is_string($decodedContent['summary'])
    ) {
        $recommendationContent = $decodedContent;
    }
}
                                @endphp
@if ($latestRecommendation?->status === 'processing')
    <div class="mb-5 rounded-xl border border-blue-500/20 bg-blue-500/[0.06] p-4 text-sm text-blue-300">
        <strong>Rekomendasi AI sedang diproses.</strong>
        Sistem sedang menyusun saran pengembangan kompetensi.
        Silakan tunggu hingga proses selesai.
    </div>
@endif
@if ($latestRecommendation?->status === 'failed')
    <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/[0.06] p-4 text-sm text-red-300">
        <strong>Rekomendasi AI gagal dibuat.</strong>
        Sistem belum berhasil menyusun rekomendasi.
        Kamu dapat mencoba membuatnya kembali melalui tombol di bawah.
    </div>
@endif
@if ($latestRecommendation?->status === 'stale')
    <div class="mb-5 rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-4 text-sm text-amber-300">
        <strong>Rekomendasi AI sudah tidak berlaku.</strong>
        Hasil analisis kompetensi telah berubah sejak rekomendasi dibuat.
        Buat rekomendasi baru agar saran pengembangan sesuai dengan hasil analisis terbaru.
    </div>
@endif
                                {{-- AI Recommendations --}}
                                @if ($recommendationContent && is_array($recommendationContent))
                                    <section class="mb-6 overflow-hidden rounded-xl border border-purple-500/20 bg-purple-500/[0.05]">
                                        <div class="border-b border-purple-500/15 p-4">
                                            <h4 class="font-semibold text-white">
                                                Rekomendasi Pengembangan dari AI
                                            </h4>
                                          <p class="mt-1 text-xs leading-5 text-slate-500">
    Saran pengembangan berdasarkan hasil analisis kompetensi.
    Rekomendasi AI bersifat pendukung dan tidak memengaruhi skor
    maupun peringkat Profile Matching.
</p>
                                        </div>

                                        <div class="space-y-5 p-4 sm:p-5">
                                            <p class="text-sm leading-6 text-slate-300">
                                                {{ $recommendationContent['summary'] ?? 'Ringkasan belum tersedia.' }}
                                            </p>

                                            @if (!empty($recommendationContent['verified_gaps']))
                                                <div>
                                                    <h5 class="mb-3 text-sm font-semibold text-purple-300">
                                                        Kompetensi Prioritas
                                                    </h5>

                                                    <div class="space-y-3">
                                                        @foreach ($recommendationContent['verified_gaps'] as $gap)
                                                            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
                                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                                    <strong class="text-sm text-white">
                                                                        {{ $gap['skill'] }}
                                                                    </strong>
                                                                    <span class="rounded-md border border-amber-500/20 bg-amber-500/10 px-2 py-1 text-xs text-amber-300">
                                                                        {{ ucfirst($gap['priority']) }}
                                                                    </span>
                                                                </div>

                                                                <p class="mt-2 text-xs text-slate-500">
                                                                    Level saat ini:
                                                                    <span class="text-slate-300">{{ $gap['actual_level'] }}</span>
                                                                    <span class="mx-1">·</span>
                                                                    Level kebutuhan:
                                                                    <span class="text-slate-300">{{ $gap['required_level'] }}</span>
                                                                </p>

                                                                <p class="mt-3 text-sm leading-6 text-slate-400">
                                                                    {{ $gap['reason'] }}
                                                                </p>

                                                                <p class="mt-3 text-xs font-semibold text-slate-300">
                                                                    Tindakan yang disarankan:
                                                                </p>
                                                                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-400">
                                                                    @foreach ($gap['actions'] as $action)
                                                                        <li>{{ $action }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!empty($recommendationContent['verification_needed']))
                                                <div>
                                                    <h5 class="mb-3 text-sm font-semibold text-amber-300">
                                                        Kompetensi yang Perlu Diverifikasi
                                                    </h5>

                                                    <div class="space-y-2">
                                                        @foreach ($recommendationContent['verification_needed'] as $item)
                                                            <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-3">
                                                                <strong class="text-sm text-white">
                                                                    {{ $item['skill'] }}
                                                                </strong>
                                                                <p class="mt-1 text-xs text-slate-400">
                                                                    Status: {{ $item['status'] }}
                                                                </p>
                                                                <p class="mt-2 text-sm text-slate-300">
                                                                    {{ $item['action'] }}
                                                                </p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!empty($recommendationContent['development_plan']))
                                                <div>
                                                    <h5 class="mb-3 text-sm font-semibold text-purple-300">
                                                        Rencana Pengembangan
                                                    </h5>

                                                    <div class="space-y-3">
                                                        @foreach ($recommendationContent['development_plan'] as $plan)
                                                            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
                                                                <span class="text-xs font-semibold uppercase tracking-wider text-purple-300">
                                                                    {{ $plan['timeframe'] }}
                                                                </span>
                                                                <p class="mt-2 text-sm font-semibold text-white">
                                                                    {{ $plan['goal'] }}
                                                                </p>
                                                                <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-400">
                                                                    @foreach ($plan['activities'] as $activity)
                                                                        <li>{{ $activity }}</li>
                                                                    @endforeach
                                                                </ul>
                                                                <p class="mt-3 text-xs leading-5 text-slate-500">
                                                                    <strong class="text-slate-300">Indikator keberhasilan:</strong>
                                                                    {{ $plan['success_indicator'] }}
                                                                </p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </section>
                                @endif

                                {{-- Result Status --}}
                                @if ($result->status === 'provisional')
                                    <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-4 text-sm text-amber-200">
                                        <strong>Skor sementara.</strong>
                                        Ada {{ $unverifiedCount }} dari {{ $details->count() }} kompetensi yang belum terverifikasi.
                                       Kompetensi tersebut belum disertakan dalam perhitungan skor. Hasil dapat berubah setelah verifikasi admin.
                                    </div>
                                @elseif ($result->status === 'final')
                                    <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] p-4 text-sm text-emerald-200">
                                        <strong>Skor final.</strong>
                                        Seluruh kompetensi yang disyaratkan telah memiliki level terverifikasi saat analisis ini dihitung.
                                    </div>
                                @elseif ($result->status === 'calculated')
                                    <div class="mb-4 rounded-xl border border-slate-700 bg-slate-800/50 p-4 text-sm text-slate-300">
                                        <strong>Hasil analisis lama.</strong>
                                        Hitung ulang untuk memperbarui status berdasarkan verifikasi kompetensi terbaru.
                                    </div>
                                @endif


                                {{-- Competency Table --}}
                                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <h4 class="font-semibold text-white">Rincian Kompetensi</h4>

    <span class="text-xs text-slate-400">
        {{ $evaluatedCount }} dari {{ $details->count() }} kompetensi dievaluasi
        ({{ number_format($coverage, 1) }}%)
    </span>
</div>

                                <div class="overflow-x-auto rounded-xl border border-slate-800">
                                    <table class="w-full min-w-[850px] text-left text-xs">
                                        <thead class="bg-slate-800/70 text-slate-400">
                                            <tr>
                                                <th class="px-4 py-3 font-medium">Kompetensi</th>
                                                <th class="px-4 py-3 font-medium">Level Mahasiswa</th>
                                                <th class="px-4 py-3 font-medium">Level Kebutuhan</th>
                                                <th class="px-4 py-3 font-medium">Gap</th>
                                                <th class="px-4 py-3 font-medium">Bobot Gap</th>
                                                <th class="px-4 py-3 font-medium">Faktor</th>
                                                <th class="px-4 py-3 font-medium">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-800 bg-slate-900/40">
                                            @foreach ($details as $detail)
                                                <tr class="text-slate-300 transition hover:bg-slate-800/40">
                                                    <td class="px-4 py-3 font-medium text-white">
                                                        {{ $detail->skill?->name ?? 'Kompetensi tidak tersedia' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $detail->actual_level ?? 'Belum ada' }}
                                                    </td>
                                                    <td class="px-4 py-3">{{ $detail->required_level }}</td>
                                                    <td class="px-4 py-3 tabular-nums">
    {{ $detail->gap !== null ? $detail->gap : '—' }}
</td>
<td class="px-4 py-3 tabular-nums">
    {{ $detail->gap_weight !== null
        ? number_format((float) $detail->gap_weight, 2)
        : '—' }}
</td>
                                                    <td class="px-4 py-3">
                                                        {{ $detail->factor_type === 'core' ? 'Core Factor' : 'Secondary Factor' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @switch($detail->evaluation_status)
                                                            @case('evaluated')
                                                                <span class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[11px] text-emerald-300">
                                                                    Terverifikasi
                                                                </span>
                                                                @break
                                                            @case('pending')
                                                                <span class="rounded-md border border-amber-500/20 bg-amber-500/10 px-2 py-1 text-[11px] text-amber-300">
                                                                    Menunggu verifikasi
                                                                </span>
                                                                @break
                                                            @case('level_missing')
                                                                <span class="rounded-md border border-red-500/20 bg-red-500/10 px-2 py-1 text-[11px] text-red-300">
                                                                    Level belum tersedia
                                                                </span>
                                                                @break
                                                            @case('rejected')
                                                                <span class="rounded-md border border-red-500/20 bg-red-500/10 px-2 py-1 text-[11px] text-red-300">
                                                                    Ditolak
                                                                </span>
                                                                @break
                                                            @default
                                                                <span class="rounded-md border border-slate-700 bg-slate-800 px-2 py-1 text-[11px] text-slate-400">
                                                                    Belum diisi
                                                                </span>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- AI Action --}}
                              @if (
    !$latestRecommendation
    || in_array($latestRecommendation->status, ['failed', 'stale'])
)
                                    <div class="mt-5 border-t border-slate-800 pt-5">
                                        <form
                                            method="POST"
                                            action="{{ route('matching.recommendations.generate', $result) }}"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="w-full rounded-xl border border-purple-500/30 bg-purple-500/10 px-4 py-3 text-sm font-semibold text-purple-300 transition hover:bg-purple-500/20 sm:w-auto"
                                            >
                                                Buat Rekomendasi AI
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $jobPostings->links() }}
            </div>
        @endif
    </section>

    {{-- Inactive Job History --}}
    @if ($inactiveResults->isNotEmpty())
        <section>
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-white">Riwayat Hasil Analisis</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Hasil dari lowongan yang sudah tidak aktif tetap disimpan sebagai riwayat
                    dan tidak dapat dihitung ulang.
                </p>
            </div>

            <div class="space-y-3">
                @foreach ($inactiveResults as $oldResult)
                    <article class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-white">
                                    {{ $oldResult->jobPosting->title }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $oldResult->jobPosting->company ?: 'Perusahaan belum dicantumkan' }}
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <span class="rounded-md border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs text-slate-400">
                                        Lowongan nonaktif
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        Waktu analisis:
                                        {{ $oldResult->calculated_at
                                            ? $oldResult->calculated_at->format('d/m/Y H:i')
                                            : 'Tidak tersedia' }}
                                    </span>
                                </div>
                            </div>

                            <div class="sm:text-right">
                                <p class="text-xs text-slate-500">Skor saat analisis</p>
                                <p class="mt-1 text-2xl font-bold tabular-nums text-white">
                                    {{ $oldResult->final_score !== null
                                        ? number_format((float) $oldResult->final_score, 2)
                                        : 'Belum tersedia' }}
                                    @if ($oldResult->final_score !== null)
                                        <span class="text-sm font-normal text-slate-500">/ 5</span>
                                    @endif
                                </p>
                                <span class="mt-2 inline-flex rounded-md border border-slate-700 bg-slate-800 px-2 py-1 text-xs text-slate-400">
                                    {{ ucfirst($oldResult->status) }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
