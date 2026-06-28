<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_security_question_can_be_requested(): void
    {
        $user = User::factory()->create([
            'security_question' => 'En que ciudad naciste?',
            'security_answer_hash' => Hash::make('lima'),
        ]);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertStatus(200);
    }

    public function test_password_can_be_reset_with_security_answer(): void
    {
        $user = User::factory()->create([
            'security_question' => 'En que ciudad naciste?',
            'security_answer_hash' => Hash::make('lima'),
        ]);

        $this->post('/reset-password', [
            'email' => $user->email,
            'security_answer' => ' Lima ',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_security_recovery_blocks_after_failed_attempts(): void
    {
        $user = User::factory()->create([
            'security_question' => 'En que ciudad naciste?',
            'security_answer_hash' => Hash::make('lima'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/reset-password', [
                'email' => $user->email,
                'security_answer' => 'mal',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);
        }

        $this->post('/reset-password', [
            'email' => $user->email,
            'security_answer' => 'lima',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('security_answer');
    }
}
