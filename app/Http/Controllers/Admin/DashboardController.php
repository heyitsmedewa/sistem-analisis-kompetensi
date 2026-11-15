<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\StudentSkill;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalJobs = JobPosting::count();

        $activeJobs = JobPosting::where('is_active', true)->count();

        $pendingVerifications = StudentSkill::where(
            'confirmation_status',
            'pending'
        )->count();

        $approvedSkills = StudentSkill::where(
            'confirmation_status',
            'approved'
        )->count();

        return view('admin.dashboard', compact(
            'totalJobs',
            'activeJobs',
            'pendingVerifications',
            'approvedSkills'
        ));
    }
}
