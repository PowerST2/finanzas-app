<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (! Wallet::where('user_id', $user->id)->exists()) {
            return redirect()->route('onboarding.create');
        }

        $month = now()->format('Y-m');
        $wallets = Wallet::where('user_id', $user->id)->orderBy('name')->get();
        $transactions = Transaction::with('wallet')->where('user_id', $user->id)->where('month', $month)->where('status', 'confirmed')->get();
        $incomeTypes = ['income', 'loan_received', 'loan_collection'];
        $expenseTypes = ['expense', 'loan_payment', 'loan_given'];
        $display = $user->currency ?: 'PEN';
        $wallets->transform(function ($wallet) use ($display) {
            $wallet->display_balance = Currency::convert((float) $wallet->current_balance_cache, $wallet->currency, $display);
            $wallet->display_currency = $display;

            return $wallet;
        });
        $recentTransactions = Transaction::with(['wallet', 'destinationWallet', 'category'])->where('user_id', $user->id)->latest('date')->limit(8)->get()
            ->map(function ($tx) use ($display) {
                $tx->display_amount = Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display);
                $tx->display_currency = $display;

                return $tx;
            });

        return Inertia::render('Dashboard', [
            'summary' => [
                'currency' => $display,
                'total' => (float) $wallets->sum(fn ($wallet) => Currency::convert((float) $wallet->current_balance_cache, $wallet->currency, $display)),
                'income' => (float) $transactions->whereIn('type', $incomeTypes)->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display)),
                'expense' => (float) $transactions->whereIn('type', $expenseTypes)->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display)),
                'debt' => (float) Loan::with('wallet')->where('user_id', $user->id)->where('kind', 'borrowed')->where('status', 'active')->get()->sum(fn ($loan) => Currency::convert((float) $loan->current_balance, $loan->wallet?->currency, $display)),
                'receivable' => (float) Loan::with('wallet')->where('user_id', $user->id)->where('kind', 'lent')->where('status', 'active')->get()->sum(fn ($loan) => Currency::convert((float) $loan->current_balance, $loan->wallet?->currency, $display)),
            ],
            'wallets' => $wallets,
            'alerts' => Alert::where('user_id', $user->id)->whereNull('read_at')->latest('triggered_at')->limit(5)->get(),
            'topExpenses' => Category::where('categories.user_id', $user->id)
                ->join('transactions', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.user_id', $user->id)
                ->where('transactions.month', $month)
                ->where('transactions.type', 'expense')
                ->groupBy('categories.id', 'categories.name')
                ->orderByRaw('sum(transactions.amount) desc')
                ->limit(5)
                ->get(['categories.id', 'categories.name'])
                ->map(fn ($row) => ['name' => $row->name, 'amount' => (float) Transaction::with('wallet')->where('user_id', $user->id)->where('month', $month)->where('type', 'expense')->where('category_id', $row->id)->get()->sum(fn ($tx) => Currency::convert((float) $tx->amount, $tx->wallet?->currency, $display))]),
            'recentTransactions' => $recentTransactions,
            'lastBackup' => collect(File::glob(storage_path('app/backups/*.sql')))->sortDesc()->first(),
        ]);
    }
}
