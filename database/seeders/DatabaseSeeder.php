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
        if (! env('SUPERADMIN_EMAIL') || ! env('SUPERADMIN_PASSWORD')) {
            return;
        }

        User::updateOrCreate(['email' => env('SUPERADMIN_EMAIL')], [
            'name' => env('SUPERADMIN_NAME'),
            'password' => Hash::make(env('SUPERADMIN_PASSWORD')),
            'is_superuser' => true,
            'is_active' => true,
            'security_question' => env('SUPERADMIN_SECURITY_QUESTION'),
            'security_answer_hash' => env('SUPERADMIN_SECURITY_ANSWER')
                ? Hash::make(mb_strtolower(trim(env('SUPERADMIN_SECURITY_ANSWER'))))
                : null,
        ]);
    }
}
