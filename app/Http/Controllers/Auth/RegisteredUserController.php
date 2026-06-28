<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\FinanceService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, FinanceService $finance): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'security_question' => ['required', 'string', 'max:255'],
            'security_answer' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_superuser' => ! User::exists(),
            'security_question' => $request->security_question,
            'security_answer_hash' => Hash::make($this->normalizeAnswer($request->security_answer)),
        ]);

        event(new Registered($user));
        $finance->ensureCategories($user);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function normalizeAnswer(string $answer): string
    {
        return mb_strtolower(trim($answer));
    }
}
