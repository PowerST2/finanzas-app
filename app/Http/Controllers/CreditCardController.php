<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CreditCardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('CreditCards/Index', [
            'wallets' => Wallet::where('user_id', $request->user()->id)
                ->where('type', '!=', 'credit_card')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'cards' => Wallet::where('user_id', $request->user()->id)
                ->where('type', 'credit_card')
                ->orderBy('name')
                ->get()
                ->map(fn ($card) => $this->cardSummary($card)),
        ]);
    }

    public function reset(Request $request, Wallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id && $wallet->type === 'credit_card', 404);
        $wallet->update(['credit_cycle_started_at' => now()]);

        return back();
    }

    public function pay(Request $request, Wallet $wallet, FinanceService $finance)
    {
        abort_unless($wallet->user_id === $request->user()->id && $wallet->type === 'credit_card', 404);

        $data = $request->validate([
            'wallet_id' => ['required', 'exists:wallets,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'date' => ['required', 'date'],
        ]);

        $source = Wallet::where('user_id', $request->user()->id)
            ->where('type', '!=', 'credit_card')
            ->findOrFail($data['wallet_id']);
        $pending = $this->pendingAmount($wallet);
        $sourceAmount = (float) \App\Support\Currency::convert((float) $data['amount'], $wallet->currency, $source->currency);

        if ($pending <= 0) {
            throw ValidationException::withMessages(['amount' => 'Esta tarjeta no tiene deuda pendiente.']);
        }

        if ((float) $data['amount'] > $pending) {
            throw ValidationException::withMessages(['amount' => 'No puedes pagar mas que la deuda pendiente.']);
        }

        if ($sourceAmount > (float) $source->current_balance_cache) {
            throw ValidationException::withMessages(['wallet_id' => 'La billetera seleccionada no tiene saldo suficiente.']);
        }

        $finance->createTransaction($request->user(), [
            'wallet_id' => $source->id,
            'destination_wallet_id' => $wallet->id,
            'category_id' => $finance->defaultCategoryId($request->user(), 'transfer'),
            'type' => 'transfer',
            'amount' => $data['amount'],
            'currency' => $wallet->currency,
            'date' => $data['date'],
            'status' => 'confirmed',
            'description' => 'Pago de tarjeta: '.$wallet->name,
        ]);

        return back();
    }

    private function cardSummary(Wallet $card): Wallet
    {
        $used = $this->pendingAmount($card);
        $start = $this->cycleStart($card);
        $cycle = $this->cycleFrom($card, $start);

        $card->used_amount = $used;
        $card->available_amount = max(0, (float) $card->opening_balance - $used);
        $card->pending_amount = $used;
        $card->cycle_transactions_count = Transaction::where('status', 'confirmed')
            ->where(fn ($query) => $query->where('wallet_id', $card->id)->orWhere('destination_wallet_id', $card->id))
            ->where('date', '>=', $start)
            ->where('date', '<', $cycle['close']->copy()->addDay())
            ->count();
        $card->cycle_start = $start->format('d/m/y');
        $card->cycle_close = $cycle['close']->format('d/m/y');
        $card->payment_due = $cycle['due']->format('d/m/y');
        $card->reset_date = $cycle['reset']->format('d/m/y');

        return $card;
    }

    private function pendingAmount(Wallet $card): float
    {
        $start = $this->cycleStart($card);
        $cycle = $this->cycleFrom($card, $start);
        $transactions = Transaction::where('status', 'confirmed')
            ->where(fn ($query) => $query->where('wallet_id', $card->id)->orWhere('destination_wallet_id', $card->id))
            ->where('date', '>=', $start)
            ->where('date', '<', $cycle['close']->copy()->addDay())
            ->get();
        $charges = (float) $transactions
            ->where('wallet_id', $card->id)
            ->whereIn('type', ['expense', 'loan_payment', 'loan_given', 'transfer'])
            ->sum('amount');
        $payments = (float) $transactions->sum(function ($transaction) use ($card) {
            if ($transaction->destination_wallet_id === $card->id && $transaction->type === 'transfer') {
                return (float) ($transaction->destination_amount ?? $transaction->amount);
            }

            return $transaction->wallet_id === $card->id && in_array($transaction->type, ['income', 'loan_received', 'loan_collection'], true)
                ? (float) $transaction->amount
                : 0;
        });

        return max(0, $charges - $payments);
    }

    private function cycleStart(Wallet $card): Carbon
    {
        $today = now()->startOfDay();
        $startDay = $card->credit_cycle_start_day ?: 1;
        $start = $this->dateForDay($today, $startDay);

        if ($today->lt($start)) {
            $start = $this->dateForDay($today->copy()->subMonthNoOverflow(), $startDay);
        }

        return $card->credit_cycle_started_at && $card->credit_cycle_started_at->gt($start)
            ? $card->credit_cycle_started_at->copy()->startOfDay()
            : $start;
    }

    private function cycleFrom(Wallet $card, Carbon $start): array
    {
        $startDay = $card->credit_cycle_start_day ?: 1;
        $closeDay = $card->credit_cycle_close_day ?: $start->copy()->endOfMonth()->day;
        $close = $this->dateForDay($start, $closeDay);
        if ($close->lte($start)) {
            $close = $this->dateForDay($start->copy()->addMonthNoOverflow(), $closeDay);
        }

        return [
            'start' => $start,
            'close' => $close,
            'due' => $this->dateAfter($close, $card->credit_payment_due_day ?: 5),
            'reset' => $this->dateAfter($close, $card->credit_reset_day ?: $startDay),
        ];
    }

    private function dateAfter(Carbon $date, int $day): Carbon
    {
        $result = $this->dateForDay($date, $day);

        return $result->lte($date) ? $this->dateForDay($date->copy()->addMonthNoOverflow(), $day) : $result;
    }

    private function dateForDay(Carbon $date, int $day): Carbon
    {
        return Carbon::create($date->year, $date->month, min($day, $date->daysInMonth))->startOfDay();
    }
}
