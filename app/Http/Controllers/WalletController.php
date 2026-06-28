<?php

namespace App\Http\Controllers;

use App\Models\CurrencyOption;
use App\Models\Wallet;
use App\Models\WalletTypeOption;
use App\Services\FinanceService;
use App\Support\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $display = $request->user()->currency ?: 'PEN';

        return Inertia::render('Wallets/Index', [
            'displayCurrency' => $display,
            'wallets' => Wallet::where('user_id', $request->user()->id)->orderBy('name')->get()->map(function ($wallet) use ($display) {
                $wallet->display_balance = Currency::convert((float) $wallet->current_balance_cache, $wallet->currency, $display);

                return $wallet;
            }),
        ]);
    }

    public function create() { return Inertia::render('Wallets/Form', $this->options()); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'exists:wallet_type_options,code'],
            'currency' => ['required', 'exists:currency_options,code'],
            'exchange_rate_to_pen' => ['required', 'numeric', 'gt:0'],
            'opening_balance' => ['required', 'numeric'],
            'is_active' => ['boolean'],
        ]);
        $data['user_id'] = $request->user()->id;
        $data['current_balance_cache'] = $data['opening_balance'];
        Wallet::create($data);

        return redirect()->route('wallets.index');
    }

    public function edit(Request $request, Wallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id, 404);

        return Inertia::render('Wallets/Form', ['wallet' => $wallet, ...$this->options()]);
    }

    public function update(Request $request, Wallet $wallet, FinanceService $finance)
    {
        abort_unless($wallet->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'exists:wallet_type_options,code'],
            'currency' => ['required', 'exists:currency_options,code'],
            'exchange_rate_to_pen' => ['required', 'numeric', 'gt:0'],
            'opening_balance' => ['required', 'numeric'],
            'is_active' => ['boolean'],
        ]);

        $wallet->update($data);
        $finance->refreshWallet($wallet);

        return redirect()->route('wallets.index');
    }

    private function options(): array
    {
        return [
            'walletTypes' => WalletTypeOption::where('is_active', true)->orderBy('name')->get(),
            'currencies' => CurrencyOption::where('is_active', true)->orderBy('code')->get(),
        ];
    }
}
