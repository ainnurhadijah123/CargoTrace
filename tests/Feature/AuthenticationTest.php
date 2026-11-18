<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'operator_test@cargotrace.com',
            'password' => Hash::make('password123'),
            'role' => 'OPERATOR',
        ]);

        $response = $this->post('/login', [
            'email' => 'operator_test@cargotrace.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('operator.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'user@cargotrace.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@cargotrace.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_user_can_register_new_customer_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Shipper',
            'email' => 'john@client.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'john@client.com',
            'role' => 'CUSTOMER',
        ]);
    }

    public function test_api_login_returns_sanctum_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api_user@cargotrace.com',
            'password' => Hash::make('secrettoken'),
            'role' => 'OPERATOR',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api_user@cargotrace.com',
            'password' => 'secrettoken',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertTrue($response['success']);
    }

    public function test_api_register_creates_account_and_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice Global',
            'email' => 'alice@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'alice@company.com',
                        'role' => 'CUSTOMER',
                    ],
                ],
            ]);
    }
}
