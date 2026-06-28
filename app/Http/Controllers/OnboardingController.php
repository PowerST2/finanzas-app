<?php

namespace App\Http\Controllers;

use App\Models\CurrencyOption;
use App\Models\WalletTypeOption;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('Onboarding/Create', [
            'walletTypes' => WalletTypeOption::where('is_active', true)->orderBy('name')->get(),
            'currencies' => CurrencyOption::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request, FinanceService $finance)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'exists:wallet_type_options,code'],
            'currency' => ['required', 'exists:currency_options,code'],
            'opening_balance' => ['required', 'numeric', 'min:-999999999.99', 'max:999999999.99'],
        ]);

        $finance->onboard($request->user(), $data);

        return redirect()->route('dashboard');
    }
}
