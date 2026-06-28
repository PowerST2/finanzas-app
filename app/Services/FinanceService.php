<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FinanceService
{
    public const INCOME_CATEGORIES = ['Sueldo', 'Freelance', 'Apoyo familiar', 'Venta', 'Reembolso', 'Prestamo recibido', 'Cobro de prestamo', 'Otros ingresos'];
    public const EXPENSE_CATEGORIES = ['Comida', 'Transporte', 'Estudios', 'Servicios', 'Salud', 'Deudas', 'Prestamo otorgado', 'Ocio', 'Suscripciones', 'Tecnologia', 'Ropa', 'Emergencias', 'Otros gastos'];

    public function ensureCategories(User $user): void
    {
        foreach ([['income', self::INCOME_CATEGORIES], ['expense', self::EXPENSE_CATEGORIES]] as [$type, $names]) {
            foreach ($names as $name) {
                Category::firstOrCreate(['user_id' => $user->id, 'name' => $name, 'type' => $type], ['is_default' => true]);
            }
        }
    }

    public function onboard(User $user, array $data): Wallet
    {
        return DB::transaction(function () use ($user, $data) {
            $this->ensureCategories($user);

            return Wallet::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'type' => $data['type'] ?? 'cash',
                'currency' => $data['currency'] ?? 'PEN',
                'exchange_rate_to_pen' => \App\Models\CurrencyOption::where('code', $data['currency'] ?? 'PEN')->value('exchange_rate_to_pen') ?? 1,
                'opening_balance' => $data['opening_balance'],
                'current_balance_cache' => $data['opening_balance'],
            ]);
        });
    }

    public function createTransaction(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $wallet = Wallet::where('user_id', $user->id)->findOrFail($data['wallet_id']);
            $destination = isset($data['destination_wallet_id']) ? Wallet::where('user_id', $user->id)->findOrFail($data['destination_wallet_id']) : null;

            if (($data['type'] ?? '') === 'transfer' && (! $destination || $destination->is($wallet))) {
                throw ValidationException::withMessages(['destination_wallet_id' => 'Elige una billetera destino distinta.']);
            }

            if (($data['type'] ?? '') === 'adjustment' && blank($data['description'] ?? null)) {
                throw ValidationException::withMessages(['description' => 'El ajuste requiere descripcion.']);
            }

            $date = Carbon::parse($data['date']);
            $inputCurrency = $data['currency'] ?? $wallet->currency;
            $walletAmount = Currency::convert((float) $data['amount'], $inputCurrency, $wallet->currency);
            $conversion = $this->conversion($wallet, $destination, $walletAmount);
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'destination_wallet_id' => $destination?->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'amount' => $walletAmount,
                'destination_amount' => $conversion['destination_amount'],
                'exchange_rate' => $conversion['exchange_rate'],
                'original_amount' => $data['amount'],
                'original_currency' => $inputCurrency,
                'date' => $date,
                'month' => $date->format('Y-m'),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'confirmed',
            ]);

            $this->refreshWallet($wallet);
            if ($destination) {
                $this->refreshWallet($destination);
            }

            $this->audit($user, $transaction, 'creado', null, $transaction->toArray());

            return $transaction;
        });
    }

    public function updateTransaction(User $user, Transaction $transaction, array $data): Transaction
    {
        abort_unless($transaction->user_id === $user->id, 404);

        return DB::transaction(function () use ($user, $transaction, $data) {
            $old = $transaction->toArray();
            $oldWallets = $this->walletsTouchedBy($transaction);
            $wallet = Wallet::where('user_id', $user->id)->findOrFail($data['wallet_id']);
            $destination = isset($data['destination_wallet_id']) ? Wallet::where('user_id', $user->id)->findOrFail($data['destination_wallet_id']) : null;

            if (($data['type'] ?? '') === 'transfer' && (! $destination || $destination->is($wallet))) {
                throw ValidationException::withMessages(['destination_wallet_id' => 'Elige una billetera destino distinta.']);
            }

            $date = Carbon::parse($data['date']);
            $inputCurrency = $data['currency'] ?? $wallet->currency;
            $walletAmount = Currency::convert((float) $data['amount'], $inputCurrency, $wallet->currency);
            $conversion = $this->conversion($wallet, $destination, $walletAmount);
            $transaction->update([
                'wallet_id' => $wallet->id,
                'destination_wallet_id' => $destination?->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'amount' => $walletAmount,
                'destination_amount' => $conversion['destination_amount'],
                'exchange_rate' => $conversion['exchange_rate'],
                'original_amount' => $data['amount'],
                'original_currency' => $inputCurrency,
                'date' => $date,
                'month' => $date->format('Y-m'),
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
            ]);

            $this->refreshWallets($oldWallets->merge($this->walletsTouchedBy($transaction))->unique('id'));
            $this->audit($user, $transaction, 'editado', $old, $transaction->fresh()->toArray());

            return $transaction;
        });
    }

    public function cancelTransaction(User $user, Transaction $transaction, string $reason): void
    {
        abort_unless($transaction->user_id === $user->id, 404);

        DB::transaction(function () use ($user, $transaction, $reason) {
            $old = $transaction->toArray();
            $transaction->forceFill([
                'status' => 'cancelled',
                'cancelled_reason' => $reason,
                'cancelled_at' => now(),
            ])->save();

            $this->refreshWallets($this->walletsTouchedBy($transaction));
            $this->audit($user, $transaction, 'anulado', $old, $transaction->fresh()->toArray(), $reason);
        });
    }

    public function createLoan(User $user, array $data): Loan
    {
        return DB::transaction(function () use ($user, $data) {
            $wallet = Wallet::where('user_id', $user->id)->findOrFail($data['wallet_id']);
            $loan = Loan::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'kind' => $data['kind'] ?? 'borrowed',
                'name' => $data['name'],
                'lender_name' => $data['lender_name'] ?? null,
                'principal_amount' => $data['principal_amount'],
                'current_balance' => $data['principal_amount'],
                'interest_rate' => $data['interest_rate'] ?? null,
                'received_at' => $data['received_at'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $date = Carbon::parse($data['received_at']);
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'loan_id' => $loan->id,
                'type' => ($data['kind'] ?? 'borrowed') === 'lent' ? 'loan_given' : 'loan_received',
                'amount' => $data['principal_amount'],
                'date' => $date,
                'month' => $date->format('Y-m'),
                'description' => (($data['kind'] ?? 'borrowed') === 'lent' ? 'Prestamo otorgado: ' : 'Prestamo recibido: ').$loan->name,
                'status' => 'confirmed',
            ]);

            $this->refreshWallet($wallet);
            $this->audit($user, $loan, 'creado', null, $loan->toArray());

            return $loan;
        });
    }

    public function payLoan(User $user, Loan $loan, array $data): LoanPayment
    {
        abort_unless($loan->user_id === $user->id, 404);

        return DB::transaction(function () use ($user, $loan, $data) {
            if ((float) $data['amount'] > (float) $loan->current_balance) {
                throw ValidationException::withMessages(['amount' => 'No puedes pagar mas que el saldo pendiente.']);
            }

            $wallet = Wallet::where('user_id', $user->id)->findOrFail($data['wallet_id']);
            $date = Carbon::parse($data['paid_at']);
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'loan_id' => $loan->id,
                'type' => $loan->kind === 'lent' ? 'loan_collection' : 'loan_payment',
                'amount' => $data['amount'],
                'date' => $date,
                'month' => $date->format('Y-m'),
                'description' => $data['notes'] ?? ($loan->kind === 'lent' ? 'Cobro de prestamo: ' : 'Pago de prestamo: ').$loan->name,
                'status' => 'confirmed',
            ]);

            $loan->current_balance = max(0, (float) $loan->current_balance - (float) $data['amount']);
            $loan->status = (float) $loan->current_balance <= 0 ? 'paid' : 'active';
            $loan->save();
            $this->refreshWallet($wallet);
            $this->audit($user, $loan, $loan->kind === 'lent' ? 'cobro registrado' : 'pago registrado', null, $transaction->toArray());

            return LoanPayment::create([
                'user_id' => $user->id,
                'loan_id' => $loan->id,
                'transaction_id' => $transaction->id,
                'amount' => $data['amount'],
                'paid_at' => $date,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function refreshWallet(Wallet $wallet): void
    {
        $balance = (float) $wallet->opening_balance;
        $transactions = Transaction::where('status', 'confirmed')
            ->where(function ($query) use ($wallet) {
                $query->where('wallet_id', $wallet->id)->orWhere('destination_wallet_id', $wallet->id);
            })
            ->get();

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;
            $balance += match ($transaction->type) {
                'income', 'loan_received', 'loan_collection' => $transaction->wallet_id === $wallet->id ? $amount : 0,
                'expense', 'loan_payment', 'loan_given' => $transaction->wallet_id === $wallet->id ? -$amount : 0,
                'transfer' => $transaction->destination_wallet_id === $wallet->id ? (float) ($transaction->destination_amount ?? $amount) : -$amount,
                'adjustment' => $amount,
                default => 0,
            };
        }

        $wallet->forceFill(['current_balance_cache' => $balance])->save();
    }

    public function generateAlerts(User $user): void
    {
        foreach ($user->wallets as $wallet) {
            if ((float) $wallet->current_balance_cache < 50) {
                Alert::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'low_balance',
                    'title' => 'Saldo bajo en '.$wallet->name,
                    'read_at' => null,
                ], [
                    'message' => 'La billetera esta por debajo de S/ 50.00.',
                    'severity' => 'warning',
                    'triggered_at' => now(),
                    'metadata' => ['wallet_id' => $wallet->id],
                ]);
            }
        }

        foreach (Loan::where('user_id', $user->id)->where('status', 'active')->whereDate('due_date', '<=', now()->addDays(7))->get() as $loan) {
            Alert::firstOrCreate([
                'user_id' => $user->id,
                'type' => 'loan_due',
                'title' => ($loan->kind === 'lent' ? 'Cobro proximo: ' : 'Pago proximo: ').$loan->name,
                'read_at' => null,
            ], [
                'message' => 'Vence el '.$loan->due_date?->format('d/m/y').'.',
                'severity' => $loan->due_date?->isPast() ? 'danger' : 'warning',
                'triggered_at' => now(),
                'metadata' => ['loan_id' => $loan->id],
            ]);
        }
    }

    private function walletsTouchedBy(Transaction $transaction)
    {
        return Wallet::whereIn('id', collect([$transaction->wallet_id, $transaction->destination_wallet_id])->filter())->get();
    }

    private function refreshWallets($wallets): void
    {
        foreach ($wallets as $wallet) {
            $this->refreshWallet($wallet);
        }
    }

    private function audit(User $user, Model $model, string $action, ?array $old = null, ?array $new = null, ?string $notes = null): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'auditable_type' => $model::class,
            'auditable_id' => $model->id,
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'notes' => $notes,
        ]);
    }

    private function conversion(Wallet $source, ?Wallet $destination, float $amount): array
    {
        if (! $destination) {
            return ['destination_amount' => null, 'exchange_rate' => null];
        }

        $rate = (float) $source->exchange_rate_to_pen / max((float) $destination->exchange_rate_to_pen, 0.0001);

        return [
            'destination_amount' => round($amount * $rate, 2),
            'exchange_rate' => round($rate, 6),
        ];
    }
}
