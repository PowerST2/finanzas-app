<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedTinyInteger('credit_cycle_start_day')->nullable()->after('credit_cycle_started_at');
            $table->unsignedTinyInteger('credit_cycle_close_day')->nullable()->after('credit_cycle_start_day');
            $table->unsignedTinyInteger('credit_payment_due_day')->nullable()->after('credit_cycle_close_day');
            $table->unsignedTinyInteger('credit_reset_day')->nullable()->after('credit_payment_due_day');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'credit_cycle_start_day',
                'credit_cycle_close_day',
                'credit_payment_due_day',
                'credit_reset_day',
            ]);
        });
    }
};
