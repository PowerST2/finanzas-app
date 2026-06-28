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
        $superadmin = config('superadmin');

        if (! $superadmin['email'] || ! $superadmin['password']) {
            return;
        }

        User::updateOrCreate(['email' => $superadmin['email']], [
            'name' => $superadmin['name'],
            'password' => Hash::make($superadmin['password']),
            'is_superuser' => true,
            'is_active' => true,
            'security_question' => $superadmin['security_question'],
            'security_answer_hash' => $superadmin['security_answer']
                ? Hash::make(mb_strtolower(trim($superadmin['security_answer'])))
                : null,
        ]);
    }
}
