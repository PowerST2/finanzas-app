<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PendingDebt;
use App\Models\Wallet;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_loan_can_be_suspended_resumed_and_deleted_without_leaving_balance_behind(): void
    {
        $user = User::factory()->create();
        $finance = app(FinanceService::class);
        $wallet = $finance->onboard($user, [
            'name' => 'Efectivo',
            'type' => 'cash',
            'currency' => 'PEN',
            'opening_balance' => 100,
        ]);
        $loan = $finance->createLoan($user, ['wallet_id' => $wallet->id, 'name' => 'Apoyo', 'principal_amount' => 200, 'received_at' => '2026-06-28']);

        $this->actingAs($user)->post("/loans/{$loan->id}/suspend")->assertRedirect();
        $this->assertSame('suspended', $loan->refresh()->status);

        $this->actingAs($user)->post("/loans/{$loan->id}/payments", [
            'wallet_id' => $wallet->id,
            'amount' => 20,
            'paid_at' => '2026-06-29',
        ])->assertSessionHasErrors('amount');

        $this->actingAs($user)->post("/loans/{$loan->id}/resume")->assertRedirect();
        $finance->payLoan($user, $loan->refresh(), ['wallet_id' => $wallet->id, 'amount' => 80, 'paid_at' => '2026-06-29']);
        $this->assertSame('220.00', $wallet->refresh()->current_balance_cache);

        $this->actingAs($user)->delete("/loans/{$loan->id}")->assertRedirect('/loans');
        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
        $this->assertSame('100.00', $wallet->refresh()->current_balance_cache);
    }

    public function test_credit_cards_are_not_part_of_available_cash_dashboard_total(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'opening_balance' => 100, 'current_balance_cache' => 100]);
        Wallet::create(['user_id' => $user->id, 'name' => 'Visa', 'type' => 'credit_card', 'currency' => 'PEN', 'opening_balance' => 1000, 'current_balance_cache' => 1000]);

        $this->actingAs($user)->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('summary.total', 100)
                ->has('creditCards', 1)
            );
    }

    public function test_credit_card_payment_moves_money_from_real_wallet_and_reduces_card_debt(): void
    {
        $user = User::factory()->create();
        $finance = app(FinanceService::class);
        $cash = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);
        $card = Wallet::create(['user_id' => $user->id, 'name' => 'Visa', 'type' => 'credit_card', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);

        $finance->createTransaction($user, ['wallet_id' => $card->id, 'type' => 'expense', 'amount' => 300, 'date' => now(), 'status' => 'confirmed']);

        $this->actingAs($user)->post(route('credit-cards.pay', $card), [
            'wallet_id' => $cash->id,
            'amount' => 300,
            'date' => now(),
        ])->assertRedirect();

        $this->assertSame('700.00', $cash->refresh()->current_balance_cache);

        $this->actingAs($user)->get(route('credit-cards.index'))
            ->assertInertia(fn (Assert $page) => $page->where('cards.0.pending_amount', 0));
    }

    public function test_credit_card_cycle_dates_roll_to_next_month_when_close_matches_start_day(): void
    {
        $this->travelTo('2026-06-29 10:00:00');

        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);
        Wallet::create([
            'user_id' => $user->id,
            'name' => 'Visa',
            'type' => 'credit_card',
            'currency' => 'PEN',
            'exchange_rate_to_pen' => 1,
            'opening_balance' => 1000,
            'current_balance_cache' => 1000,
            'credit_cycle_started_at' => now(),
            'credit_cycle_start_day' => 11,
            'credit_cycle_close_day' => 11,
            'credit_payment_due_day' => 5,
            'credit_reset_day' => 12,
        ]);

        $this->actingAs($user)->get(route('credit-cards.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cards.0.cycle_start', '29/06/26')
                ->where('cards.0.cycle_close', '11/07/26')
                ->where('cards.0.payment_due', '05/08/26')
                ->where('cards.0.reset_date', '12/07/26')
            );
    }

    public function test_credit_card_without_debt_can_not_be_paid(): void
    {
        $user = User::factory()->create();
        $cash = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);
        $card = Wallet::create(['user_id' => $user->id, 'name' => 'Visa', 'type' => 'credit_card', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);

        $this->actingAs($user)->post(route('credit-cards.pay', $card), [
            'wallet_id' => $cash->id,
            'amount' => 10,
            'date' => now(),
        ])->assertSessionHasErrors('amount');

        $this->assertSame('1000.00', $cash->refresh()->current_balance_cache);
    }

    public function test_credit_card_payment_requires_enough_real_wallet_balance(): void
    {
        $user = User::factory()->create();
        $finance = app(FinanceService::class);
        $cash = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 50, 'current_balance_cache' => 50]);
        $card = Wallet::create(['user_id' => $user->id, 'name' => 'Visa', 'type' => 'credit_card', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 1000, 'current_balance_cache' => 1000]);

        $finance->createTransaction($user, ['wallet_id' => $card->id, 'type' => 'expense', 'amount' => 300, 'date' => now(), 'status' => 'confirmed']);

        $this->actingAs($user)->post(route('credit-cards.pay', $card), [
            'wallet_id' => $cash->id,
            'amount' => 300,
            'date' => now(),
        ])->assertSessionHasErrors('wallet_id');

        $this->assertSame('50.00', $cash->refresh()->current_balance_cache);
    }

    public function test_expense_can_not_exceed_wallet_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 50, 'current_balance_cache' => 50]);
        $categoryId = app(FinanceService::class)->defaultCategoryId($user, 'expense');

        $this->actingAs($user)->post(route('transactions.store'), [
            'wallet_id' => $wallet->id,
            'category_id' => $categoryId,
            'type' => 'expense',
            'amount' => 60,
            'currency' => 'PEN',
            'date' => now(),
            'status' => 'confirmed',
        ])->assertSessionHasErrors('amount');

        $this->assertSame('50.00', $wallet->refresh()->current_balance_cache);
    }

    public function test_credit_card_expense_can_not_exceed_available_line(): void
    {
        $user = User::factory()->create();
        $card = Wallet::create(['user_id' => $user->id, 'name' => 'Visa', 'type' => 'credit_card', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 100, 'current_balance_cache' => 100]);
        $categoryId = app(FinanceService::class)->defaultCategoryId($user, 'expense');

        $this->actingAs($user)->post(route('transactions.store'), [
            'wallet_id' => $card->id,
            'category_id' => $categoryId,
            'type' => 'expense',
            'amount' => 150,
            'currency' => 'PEN',
            'date' => now(),
            'status' => 'confirmed',
        ])->assertSessionHasErrors('amount');

        $this->assertSame('100.00', $card->refresh()->current_balance_cache);
    }

    public function test_pending_debt_can_be_paid_partially_from_real_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 100, 'current_balance_cache' => 100]);
        $debt = PendingDebt::create(['user_id' => $user->id, 'name' => 'Recibo', 'total_amount' => 80, 'current_balance' => 80, 'currency' => 'PEN', 'status' => 'active']);

        $this->actingAs($user)->post(route('pending-debts.pay', $debt), [
            'wallet_id' => $wallet->id,
            'amount' => 30,
            'paid_at' => now(),
        ])->assertRedirect();

        $this->assertSame('70.00', $wallet->refresh()->current_balance_cache);
        $this->assertSame('50.00', $debt->refresh()->current_balance);
        $this->assertSame('active', $debt->status);
    }

    public function test_pending_debt_payment_requires_enough_wallet_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 20, 'current_balance_cache' => 20]);
        $debt = PendingDebt::create(['user_id' => $user->id, 'name' => 'Recibo', 'total_amount' => 80, 'current_balance' => 80, 'currency' => 'PEN', 'status' => 'active']);

        $this->actingAs($user)->post(route('pending-debts.pay', $debt), [
            'wallet_id' => $wallet->id,
            'amount' => 30,
            'paid_at' => now(),
        ])->assertSessionHasErrors('wallet_id');

        $this->assertSame('20.00', $wallet->refresh()->current_balance_cache);
        $this->assertSame('80.00', $debt->refresh()->current_balance);
    }

    public function test_pending_debt_can_be_suspended_resumed_and_deleted_without_leaving_balance_behind(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'name' => 'Efectivo', 'type' => 'cash', 'currency' => 'PEN', 'exchange_rate_to_pen' => 1, 'opening_balance' => 100, 'current_balance_cache' => 100]);
        $debt = PendingDebt::create(['user_id' => $user->id, 'name' => 'Recibo', 'total_amount' => 80, 'current_balance' => 80, 'currency' => 'PEN', 'status' => 'active']);

        $this->actingAs($user)->post("/pending-debts/{$debt->id}/suspend")->assertRedirect();
        $this->assertSame('suspended', $debt->refresh()->status);
        $this->actingAs($user)->post("/pending-debts/{$debt->id}/resume")->assertRedirect();

        $this->actingAs($user)->post("/pending-debts/{$debt->id}/payments", [
            'wallet_id' => $wallet->id,
            'amount' => 30,
            'paid_at' => now(),
        ])->assertRedirect();
        $this->assertSame('70.00', $wallet->refresh()->current_balance_cache);

        $this->actingAs($user)->delete("/pending-debts/{$debt->id}")->assertRedirect();
        $this->assertDatabaseMissing('pending_debts', ['id' => $debt->id]);
        $this->assertSame('100.00', $wallet->refresh()->current_balance_cache);
    }
}
