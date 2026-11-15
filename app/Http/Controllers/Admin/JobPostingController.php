<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Skill;
use App\Models\JobSkill;

class JobPostingController extends Controller
{
    public function index(): View
    {
        $jobPostings = JobPosting::query()
            ->withCount('jobSkills')
            ->withCount('matchingResults')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.job-postings.index', compact('jobPostings'));
    }

    public function create(): View
{
    return view('admin.job-postings.create');
}


public function edit(JobPosting $jobPosting): View
{
    return view('admin.job-postings.edit', compact('jobPosting'));
}


public function update(Request $request, JobPosting $jobPosting): RedirectResponse
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'company' => ['nullable', 'string', 'max:200'],
        'location' => ['nullable', 'string', 'max:200'],
        'description' => ['nullable', 'string'],
        'education_requirement' => ['nullable', 'string', 'max:200'],
        'experience_requirement' => ['nullable', 'string'],
        'source_name' => ['nullable', 'string', 'max:100'],
'source_url' => ['nullable', 'url', 'max:500'],
'collected_at' => ['nullable', 'date'],
    ]);

    $jobPosting->update($validated);

    return redirect()
        ->route('admin.job-postings.show', $jobPosting)
        ->with('success', 'Informasi lowongan berhasil diperbarui.');
}


public function toggleStatus(JobPosting $jobPosting): RedirectResponse
{
    $jobPosting->update([
        'is_active' => ! $jobPosting->is_active,
    ]);

    $message = $jobPosting->is_active
        ? 'Lowongan berhasil diaktifkan kembali.'
        : 'Lowongan berhasil dinonaktifkan.';

    return redirect()
        ->route('admin.job-postings.show', $jobPosting)
        ->with('success', $message);
}


public function show(JobPosting $jobPosting): View
{
    $jobPosting->load([
        'jobSkills.skill',
    ]);

    $skills = Skill::query()
        ->where('is_active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    return view('admin.job-postings.show', compact(
        'jobPosting',
        'skills'
    ));
}


public function addSkill(Request $request, JobPosting $jobPosting): RedirectResponse
{
    $validated = $request->validate([
        'skill_id' => [
            'required',
            'integer',
            'exists:skills,id',
        ],
        'required_level' => [
            'required',
            'integer',
            'between:1,5',
        ],
        'is_core_factor' => [
            'required',
            'boolean',
        ],
        'notes' => [
            'nullable',
            'string',
        ],
    ]);

    $skill = Skill::query()
        ->where('is_active', true)
        ->findOrFail($validated['skill_id']);

    $alreadyExists = $jobPosting->jobSkills()
        ->where('skill_id', $skill->id)
        ->exists();

    if ($alreadyExists) {
        return back()
            ->withInput()
            ->with('error', 'Kompetensi tersebut sudah ada pada lowongan ini.');
    }

    $jobPosting->jobSkills()->create($validated);

    return redirect()
        ->route('admin.job-postings.show', $jobPosting)
        ->with('success', 'Kompetensi berhasil ditambahkan.');
}

public function updateSkill(
    Request $request,
    JobPosting $jobPosting,
    JobSkill $jobSkill
): RedirectResponse {
    abort_unless(
        $jobSkill->job_posting_id === $jobPosting->id,
        404
    );

    $validated = $request->validate([
        'required_level' => [
            'required',
            'integer',
            'between:1,5',
        ],
        'is_core_factor' => [
            'required',
            'boolean',
        ],
        'notes' => [
            'nullable',
            'string',
        ],
    ]);

    $jobSkill->update($validated);

    return redirect()
        ->route('admin.job-postings.show', $jobPosting)
        ->with('success', 'Persyaratan kompetensi berhasil diperbarui.');
}

public function deleteSkill(
    JobPosting $jobPosting,
    JobSkill $jobSkill
): RedirectResponse {
    abort_unless(
        $jobSkill->job_posting_id === $jobPosting->id,
        404
    );

    $jobSkill->delete();

    return redirect()
        ->route('admin.job-postings.show', $jobPosting)
        ->with('success', 'Persyaratan kompetensi berhasil dihapus.');
}

public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'company' => ['nullable', 'string', 'max:200'],
        'location' => ['nullable', 'string', 'max:200'],
        'description' => ['nullable', 'string'],
        'education_requirement' => ['nullable', 'string', 'max:200'],
        'experience_requirement' => ['nullable', 'string'],

        'source_name' => ['nullable', 'string', 'max:100'],
        'source_url' => ['nullable', 'url', 'max:500'],
        'collected_at' => ['nullable', 'date'],
    ]);

    $jobPosting = JobPosting::create($validated);

    return redirect()
        ->route('admin.job-postings.index')
        ->with('success', 'Lowongan kerja berhasil ditambahkan.');
}


}
