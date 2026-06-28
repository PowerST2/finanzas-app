<?php

namespace App\Http\Controllers;

use App\Models\CurrencyOption;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTypeOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return Inertia::render('Admin/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'is_superuser', 'is_active']),
            'walletTypes' => WalletTypeOption::orderBy('name')->get(),
            'currencies' => CurrencyOption::orderBy('code')->get(),
        ]);
    }

    public function storeWalletType(Request $request)
    {
        $this->authorizeAdmin($request);
        WalletTypeOption::create($request->validate([
            'code' => ['required', 'alpha_dash', 'max:40', 'unique:wallet_type_options,code'],
            'name' => ['required', 'string', 'max:80'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function updateWalletType(Request $request, WalletTypeOption $walletType)
    {
        $this->authorizeAdmin($request);
        $walletType->update($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function storeCurrency(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->merge([
            'code' => mb_strtoupper((string) $request->code),
            'exchange_rate_to_pen' => str_replace(',', '.', (string) $request->exchange_rate_to_pen),
        ]);
        CurrencyOption::create($request->validate([
            'code' => ['required', 'size:3', 'unique:currency_options,code'],
            'name' => ['required', 'string', 'max:80'],
            'exchange_rate_to_pen' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function updateCurrency(Request $request, CurrencyOption $currency)
    {
        $this->authorizeAdmin($request);
        $request->merge(['exchange_rate_to_pen' => str_replace(',', '.', (string) $request->exchange_rate_to_pen)]);
        $currency->update($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'exchange_rate_to_pen' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['boolean'],
        ]));
        Wallet::where('currency', $currency->code)->update(['exchange_rate_to_pen' => $currency->exchange_rate_to_pen]);

        return back();
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique(User::class)->ignore($user->id)],
            'is_superuser' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($user->is_superuser && ! ($data['is_superuser'] ?? false) && User::where('is_superuser', true)->whereKeyNot($user->id)->doesntExist()) {
            throw ValidationException::withMessages(['is_superuser' => 'Debe quedar al menos un superusuario.']);
        }

        $user->update($data);

        return back();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_superuser, 403);
    }
}
