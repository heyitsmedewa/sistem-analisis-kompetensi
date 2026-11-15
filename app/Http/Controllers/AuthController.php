<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'student_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:student_profiles,student_number',
            ],
            'study_program' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $role = Role::where('name', 'mahasiswa')->firstOrFail();

            $user->roles()->attach($role->id);

            StudentProfile::create([
                'user_id' => $user->id,
                'student_number' => $validated['student_number'] ?? null,
                'study_program' => $validated['study_program'] ?? null,
                'institution' => $validated['institution'] ?? null,
            ]);
        });

        $user = User::where('email', $validated['email'])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        $redirectTo = $request->user()->roles()
            ->where('name', 'admin')
            ->exists()
                ? '/admin'
                : '/dashboard';

        return redirect()->intended($redirectTo);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}