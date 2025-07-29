<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'preferred_language' => 'en',
            'agree_terms' => true
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'user',
                    'redirect'
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }
}
