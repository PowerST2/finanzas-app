<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Console\Command;

class GenerateFinanceAlerts extends Command
{
    protected $signature = 'finance:generate-alerts';

    protected $description = 'Genera alertas internas de finanzas';

    public function handle(FinanceService $finance): int
    {
        User::with('wallets')->each(fn (User $user) => $finance->generateAlerts($user));
        $this->info('Alertas generadas.');

        return self::SUCCESS;
    }
}
