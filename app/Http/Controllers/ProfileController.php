<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function visual(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_label' => ['required', 'string', 'max:60'],
            'theme_color' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            File::ensureDirectoryExists(public_path('brand'));
            $path = 'brand/logo-user-'.$request->user()->id.'.'.$request->file('logo')->extension();
            $request->file('logo')->move(public_path('brand'), basename($path));
            $data['logo_path'] = '/'.$path;
        }

        $request->user()->update($data);

        return Redirect::route('profile.edit');
    }

    public function securityQuestion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'security_question' => ['required', 'string', 'max:255'],
            'security_answer' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $request->user()->update([
            'security_question' => $data['security_question'],
            'security_answer_hash' => Hash::make(mb_strtolower(trim($data['security_answer']))),
        ]);

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
