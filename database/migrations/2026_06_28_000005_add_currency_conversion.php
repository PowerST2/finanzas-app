<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('exchange_rate_to_pen', 12, 4)->default(1)->after('currency');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('destination_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('exchange_rate', 12, 6)->nullable()->after('destination_amount');
        });

        DB::table('wallets')->where('currency', 'USD')->update(['exchange_rate_to_pen' => 3.70]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['destination_amount', 'exchange_rate']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('exchange_rate_to_pen');
        });
    }
};
