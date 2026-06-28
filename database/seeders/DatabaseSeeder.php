<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => env('SUPERADMIN_EMAIL', 'admin@finanzas.local')], [
            'name' => env('SUPERADMIN_NAME', 'Superadmin'),
            'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'password')),
            'is_superuser' => true,
            'is_active' => true,
            'security_question' => env('SUPERADMIN_SECURITY_QUESTION', 'En que ciudad naciste?'),
            'security_answer_hash' => Hash::make(mb_strtolower(trim(env('SUPERADMIN_SECURITY_ANSWER', 'lima')))),
        ]);
    }
}
