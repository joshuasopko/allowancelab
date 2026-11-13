<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'form_rendered_at' => now()->subSeconds(5)->timestamp,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_is_rejected_when_honeypot_field_is_filled(): void
    {
        $this->post('/register', [
            'first_name' => 'Bot',
            'last_name' => 'User',
            'email' => 'bot@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'form_rendered_at' => now()->subSeconds(5)->timestamp,
            'website' => 'http://spam.example.com',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
    }

    public function test_registration_is_rejected_when_submitted_too_quickly(): void
    {
        $this->post('/register', [
            'first_name' => 'Bot',
            'last_name' => 'User',
            'email' => 'fast-bot@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'form_rendered_at' => now()->timestamp,
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'fast-bot@example.com']);
    }
}
