<?php

namespace App\Http\Controllers;

use App\Models\CurrencyOption;
use App\Models\PendingDebt;
use App\Models\PendingDebtPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PendingDebtController extends Controller
{
    public function index(Request $request)
    {
        $rates = CurrencyOption::pluck('exchange_rate_to_pen', 'code');

        return Inertia::render('PendingDebts/Index', [
            'debts' => PendingDebt::with('payments')->where('user_id', $request->user()->id)->latest()->get()->map(function ($debt) use ($rates) {
                $debt->exchange_rate_to_pen = (float) ($rates[$debt->currency] ?? 1);

                return $debt;
            }),
            'wallets' => Wallet::where('user_id', $request->user()->id)->where('type', '!=', 'credit_card')->where('is_active', true)->orderBy('name')->get(),
            'currencies' => CurrencyOption::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'total_amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'exists:currency_options,code'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        PendingDebt::create([
            ...$data,
            'user_id' => $request->user()->id,
            'current_balance' => $data['total_amount'],
            'status' => 'active',
        ]);

        return back();
    }

    public function pay(Request $request, PendingDebt $debt, FinanceService $finance)
    {
        abort_unless($debt->user_id === $request->user()->id && $debt->status === 'active', 404);

        $data = $request->validate([
            'wallet_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $wallet = Wallet::where('user_id', $request->user()->id)->where('type', '!=', 'credit_card')->findOrFail($data['wallet_id']);
        $walletAmount = Currency::convert((float) $data['amount'], $debt->currency, $wallet->currency);

        if ((float) $data['amount'] > (float) $debt->current_balance) {
            throw ValidationException::withMessages(['amount' => 'No puedes pagar mas que la deuda pendiente.']);
        }

        if ($walletAmount > (float) $wallet->current_balance_cache) {
            throw ValidationException::withMessages(['wallet_id' => 'La billetera seleccionada no tiene saldo suficiente.']);
        }

        DB::transaction(function () use ($request, $debt, $finance, $wallet, $data) {
            $transaction = $finance->createTransaction($request->user(), [
                'wallet_id' => $wallet->id,
                'category_id' => $finance->defaultCategoryId($request->user(), 'loan_payment'),
                'type' => 'expense',
                'amount' => $data['amount'],
                'currency' => $debt->currency,
                'date' => $data['paid_at'],
                'status' => 'confirmed',
                'description' => 'Pago de deuda: '.$debt->name,
            ]);

            $debt->current_balance = max(0, (float) $debt->current_balance - (float) $data['amount']);
            $debt->status = (float) $debt->current_balance <= 0 ? 'paid' : 'active';
            $debt->save();

            PendingDebtPayment::create([
                'user_id' => $request->user()->id,
                'pending_debt_id' => $debt->id,
                'transaction_id' => $transaction->id,
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back();
    }

    public function suspend(Request $request, PendingDebt $debt)
    {
        abort_unless($debt->user_id === $request->user()->id, 404);
        if ($debt->status === 'active') {
            $debt->update(['status' => 'suspended']);
        }

        return back();
    }

    public function resume(Request $request, PendingDebt $debt)
    {
        abort_unless($debt->user_id === $request->user()->id, 404);
        if ($debt->status === 'suspended') {
            $debt->update(['status' => 'active']);
        }

        return back();
    }

    public function destroy(Request $request, PendingDebt $debt, FinanceService $finance)
    {
        abort_unless($debt->user_id === $request->user()->id, 404);

        DB::transaction(function () use ($debt, $finance) {
            $transactions = Transaction::whereIn('id', $debt->payments()->pluck('transaction_id'));
            $wallets = Wallet::whereIn('id', $transactions->pluck('wallet_id'))->get();
            $transactions->delete();
            $debt->delete();
            $wallets->each(fn (Wallet $wallet) => $finance->refreshWallet($wallet));
        });

        return back();
    }
}
