<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip', 45)->nullable();
            $table->boolean('successful')->default(false);
            $table->timestamps();
            $table->index(['email', 'ip', 'successful', 'created_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable()->after('exchange_rate');
            $table->string('original_currency', 3)->nullable()->after('original_amount');
        });

        DB::table('currency_options')->updateOrInsert(
            ['code' => 'EUR'],
            ['name' => 'Euros', 'exchange_rate_to_pen' => 4.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'original_currency']);
        });

        Schema::dropIfExists('recovery_attempts');
    }
};
