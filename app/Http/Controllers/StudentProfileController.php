<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    
public function show()
{
    $profile = auth()->user()->studentProfile;

    abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

    return view('profile.show', compact('profile'));
}


public function update(Request $request)
{
    $profile = auth()->user()->studentProfile;

    abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

    $validated = $request->validate([
        'student_number' => [
            'nullable',
            'string',
            'max:255',
            'unique:student_profiles,student_number,' . $profile->id,
        ],
        'study_program' => ['nullable', 'string', 'max:255'],
        'institution' => ['nullable', 'string', 'max:255'],
        'education_level' => ['nullable', 'string', 'max:255'],
    ]);

    $profile->update($validated);

    return redirect()
        ->route('profile.show')
        ->with('success', 'Profil berhasil diperbarui.');
}

}
