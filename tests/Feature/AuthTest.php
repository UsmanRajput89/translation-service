<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic feature test example.
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $this->assertArrayHasKey('token', $response->json());
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $this->assertEquals('Invalid credentials', $response->json('message'));
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
        $this->assertEquals('Invalid credentials', $response->json('message'));
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertOk();
        $this->assertEquals('Logged out successfully', $response->json('message'));
    }


    public function test_user_cannot_logout_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

}
