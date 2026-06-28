<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_balance_and_loan_balance_follow_transactions(): void
    {
        $user = User::factory()->create();
        $finance = app(FinanceService::class);
        $wallet = $finance->onboard($user, [
            'name' => 'Dinero actual',
            'type' => 'cash',
            'currency' => 'PEN',
            'opening_balance' => 100,
        ]);

        $finance->createTransaction($user, ['wallet_id' => $wallet->id, 'type' => 'income', 'amount' => 50, 'date' => '2026-06-28', 'status' => 'confirmed']);
        $finance->createTransaction($user, ['wallet_id' => $wallet->id, 'type' => 'expense', 'amount' => 30, 'date' => '2026-06-28', 'status' => 'confirmed']);
        $loan = $finance->createLoan($user, ['wallet_id' => $wallet->id, 'name' => 'Apoyo', 'principal_amount' => 200, 'received_at' => '2026-06-28']);
        $finance->payLoan($user, $loan, ['wallet_id' => $wallet->id, 'amount' => 80, 'paid_at' => '2026-06-28']);

        $this->assertSame('240.00', $wallet->refresh()->current_balance_cache);
        $this->assertSame('120.00', $loan->refresh()->current_balance);
        $this->assertSame('active', $loan->status);
    }

    public function test_lent_loan_decreases_wallet_and_collections_increase_it(): void
    {
        $user = User::factory()->create();
        $finance = app(FinanceService::class);
        $wallet = $finance->onboard($user, [
            'name' => 'Yape',
            'type' => 'digital_wallet',
            'currency' => 'PEN',
            'opening_balance' => 500,
        ]);

        $loan = $finance->createLoan($user, [
            'wallet_id' => $wallet->id,
            'kind' => 'lent',
            'name' => 'Prestamo a Juan',
            'principal_amount' => 200,
            'received_at' => '2026-06-28 10:15',
        ]);
        $finance->payLoan($user, $loan, ['wallet_id' => $wallet->id, 'amount' => 50, 'paid_at' => '2026-06-28 11:30']);

        $this->assertSame('350.00', $wallet->refresh()->current_balance_cache);
        $this->assertSame('150.00', $loan->refresh()->current_balance);
    }
}
