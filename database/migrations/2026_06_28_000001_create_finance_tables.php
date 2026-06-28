<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('cash');
            $table->string('currency', 3)->default('PEN');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance_cache', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('lender_name')->nullable();
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('received_at');
            $table->date('due_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('month', 7);
            $table->text('description')->nullable();
            $table->string('status')->default('confirmed');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'month', 'type']);
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->decimal('total_limit', 12, 2)->nullable();
            $table->decimal('saving_goal', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('limit_amount', 12, 2);
            $table->boolean('alert_80')->default(false);
            $table->boolean('alert_90')->default(false);
            $table->boolean('alert_100')->default(false);
            $table->timestamps();
            $table->unique(['budget_id', 'category_id']);
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('severity')->default('info');
            $table->timestamp('triggered_at');
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('wallets');
    }
};
