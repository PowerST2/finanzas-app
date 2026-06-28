<?php

namespace App\Console\Commands;

use App\Models\RecurringRule;
use App\Services\FinanceService;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'finance:generate-recurring';

    protected $description = 'Genera movimientos recurrentes vencidos';

    public function handle(FinanceService $finance): int
    {
        RecurringRule::with('wallet.user')->where('is_active', true)->where('next_at', '<=', now())->get()->each(function (RecurringRule $rule) use ($finance) {
            $finance->createTransaction($rule->wallet->user, [
                'wallet_id' => $rule->wallet_id,
                'category_id' => $rule->category_id,
                'type' => $rule->type,
                'amount' => $rule->amount,
                'date' => $rule->next_at,
                'description' => $rule->description,
                'status' => 'confirmed',
            ]);

            $rule->last_generated_at = now();
            $rule->next_at = match ($rule->frequency) {
                'daily' => $rule->next_at->addDay(),
                'weekly' => $rule->next_at->addWeek(),
                default => $rule->next_at->addMonth(),
            };
            $rule->save();
        });

        $this->info('Movimientos recurrentes generados.');

        return self::SUCCESS;
    }
}
