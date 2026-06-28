<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('kind')->default('borrowed')->after('wallet_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table transactions alter column date type timestamp(0) without time zone using date::timestamp');
            DB::statement('alter table loans alter column received_at type timestamp(0) without time zone using received_at::timestamp');
            DB::statement('alter table loan_payments alter column paid_at type timestamp(0) without time zone using paid_at::timestamp');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table loan_payments alter column paid_at type date using paid_at::date');
            DB::statement('alter table loans alter column received_at type date using received_at::date');
            DB::statement('alter table transactions alter column date type date using date::date');
        }

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
