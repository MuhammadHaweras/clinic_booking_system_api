<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'provider', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function test_valid_registration_creates_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'role',
                'token',
            ])
            ->assertJsonFragment(['role' => 'customer']);

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function test_registration_returns_422_when_email_is_already_taken(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('email');
    }

    public function test_registration_returns_422_when_password_confirmation_does_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('password');
    }

    public function test_registration_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_returns_422_when_password_is_too_short(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('password');
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function test_valid_credentials_return_token_and_user(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret1234'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);
    }

    public function test_login_returns_401_when_password_is_wrong(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    public function test_login_returns_401_when_email_does_not_exist(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'The provided credentials are incorrect.']);
    }

    public function test_login_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_returns_401_when_unauthenticated(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Protected route
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_unauthenticated_request_to_user_route_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
