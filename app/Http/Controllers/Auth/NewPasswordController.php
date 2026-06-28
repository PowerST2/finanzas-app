<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RecoveryAttempt;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'security_answer' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = mb_strtolower($request->email);
        $ip = $request->ip();

        if (RecoveryAttempt::where('email', $email)->where('ip', $ip)->where('successful', false)->where('created_at', '>=', now()->subMinutes(15))->count() >= 5) {
            throw ValidationException::withMessages(['security_answer' => 'Demasiados intentos fallidos. Intenta nuevamente en 15 minutos.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check(mb_strtolower(trim($request->security_answer)), (string) $user->security_answer_hash)) {
            RecoveryAttempt::create(['email' => $email, 'ip' => $ip, 'successful' => false]);
            throw ValidationException::withMessages(['security_answer' => 'La respuesta de seguridad no coincide.']);
        }

        RecoveryAttempt::create(['email' => $email, 'ip' => $ip, 'successful' => true]);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Contrasena actualizada.');
    }
}
