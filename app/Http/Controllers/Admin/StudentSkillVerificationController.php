<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Services\MatchingSynchronizationService;

class StudentSkillVerificationController extends Controller
{
    // Daftar kompetensi yang menunggu verifikasi
    public function index()
    {
        $pendingSkills = StudentSkill::with([
            'studentProfile.user',
            'skill',
        ])
            ->where('confirmation_status', 'pending')
            ->orderBy('created_at')
            ->paginate(20);

        return view('admin.student-skills.index', compact('pendingSkills'));
    }

    // Riwayat kompetensi yang sudah disetujui atau ditolak
    public function history()
    {
        $verifiedSkills = StudentSkill::with([
            'studentProfile.user',
            'skill',
        ])
            ->whereIn('confirmation_status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view(
            'admin.student-skills.history',
            compact('verifiedSkills')
        );
    }

    // Menyetujui kompetensi
 // Menyetujui kompetensi
public function approve(
    StudentSkill $studentSkill,
    MatchingSynchronizationService $synchronizationService
): RedirectResponse {
    $level = filter_var(
        $studentSkill->actual_level,
        FILTER_VALIDATE_INT
    );

    if ($level === false || $level < 1 || $level > 5) {
        return redirect()
            ->route('admin.skills.verification.index')
            ->with(
                'error',
                'Kompetensi tidak dapat disetujui. Level harus berupa bilangan bulat 1-5.'
            );
    }

    $updated = DB::transaction(function () use (
        $studentSkill,
        $synchronizationService
    ) {
        $studentProfile = $studentSkill->studentProfile;

        $updated = DB::table('student_skills')
            ->where('id', $studentSkill->id)
            ->where('confirmation_status', 'pending')
            ->whereBetween('actual_level', [1, 5])
            ->update([
                'confirmation_status' => 'approved',
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return false;
        }

        $synchronizationService->synchronize($studentProfile);

        return true;
    });

    if (! $updated) {
        return redirect()
            ->route('admin.skills.verification.index')
            ->with(
                'error',
                'Kompetensi sudah diproses atau tidak memenuhi syarat persetujuan.'
            );
    }

    return redirect()
        ->route('admin.skills.verification.index')
        ->with(
            'success',
            'Kompetensi berhasil disetujui dan hasil matching mahasiswa telah diperbarui.'
        );
}

    // Menolak kompetensi
   public function reject(
    StudentSkill $studentSkill,
    MatchingSynchronizationService $synchronizationService
): RedirectResponse {
    $updated = DB::transaction(function () use (
        $studentSkill,
        $synchronizationService
    ) {
        $studentProfile = $studentSkill->studentProfile;

        $updated = DB::table('student_skills')
            ->where('id', $studentSkill->id)
            ->where('confirmation_status', 'pending')
            ->update([
                'confirmation_status' => 'rejected',
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return false;
        }

        $synchronizationService->synchronize($studentProfile);

        return true;
    });

    if (! $updated) {
        return redirect()
            ->route('admin.skills.verification.index')
            ->with('error', 'Kompetensi sudah diproses atau tidak ditemukan.');
    }

    return redirect()
        ->route('admin.skills.verification.index')
        ->with(
            'success',
            'Kompetensi berhasil ditolak dan hasil matching mahasiswa telah diperbarui.'
        );
}
}
