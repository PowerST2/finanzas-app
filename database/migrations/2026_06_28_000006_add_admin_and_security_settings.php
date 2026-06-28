<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superuser')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('security_question')->nullable();
            $table->string('security_answer_hash')->nullable();
        });

        Schema::create('wallet_type_options', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('currency_options', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->decimal('exchange_rate_to_pen', 12, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ([
            ['cash', 'Efectivo'],
            ['bank', 'Banco'],
            ['digital_wallet', 'Billetera digital'],
            ['credit_card', 'Tarjeta de credito'],
            ['savings', 'Ahorros'],
            ['other', 'Otro'],
        ] as [$code, $name]) {
            DB::table('wallet_type_options')->insert(['code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach ([['PEN', 'Soles', 1], ['USD', 'Dolares', 3.70]] as [$code, $name, $rate]) {
            DB::table('currency_options')->insert(['code' => $code, 'name' => $name, 'exchange_rate_to_pen' => $rate, 'created_at' => now(), 'updated_at' => now()]);
        }

        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId) {
            DB::table('users')->where('id', $firstUserId)->update(['is_superuser' => true]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_options');
        Schema::dropIfExists('wallet_type_options');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_superuser', 'is_active', 'security_question', 'security_answer_hash']);
        });
    }
};
