<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('app_label')->default('Finanzas PWA');
            $table->string('theme_color', 20)->default('#0f766e');
            $table->string('logo_path')->nullable();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->decimal('income', 12, 2)->default(0);
            $table->decimal('expense', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closings');
        Schema::dropIfExists('transaction_attachments');
        Schema::dropIfExists('audit_logs');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['cancelled_reason', 'cancelled_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_label', 'theme_color', 'logo_path']);
        });
    }
};
