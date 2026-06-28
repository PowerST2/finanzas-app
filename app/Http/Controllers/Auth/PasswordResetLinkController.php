<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'email' => session('reset_email'),
            'question' => session('reset_question'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response|RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->security_question) {
            throw ValidationException::withMessages(['email' => 'No hay pregunta de seguridad configurada para este correo.']);
        }

        return Inertia::render('Auth/ForgotPassword', [
            'email' => $user->email,
            'question' => $user->security_question,
            'status' => null,
        ]);
    }
}
