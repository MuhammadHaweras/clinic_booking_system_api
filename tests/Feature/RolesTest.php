<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the three application roles before each test.
     * This mirrors what RoleSeeder does so factory states work.
     */
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'provider', 'customer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    // -------------------------------------------------------------------------
    // Role assignment via registration
    // -------------------------------------------------------------------------

    public function test_registration_assigns_customer_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['role' => 'customer']);

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('provider'));
    }

    public function test_public_registration_cannot_assign_admin_role(): void
    {
        // Even if a client sends role=admin in the payload it must be ignored.
        $response = $this->postJson('/api/register', [
            'name' => 'Eve',
            'email' => 'eve@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'eve@example.com')->first();
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('customer'));
    }

    // -------------------------------------------------------------------------
    // Factory states
    // -------------------------------------------------------------------------

    public function test_admin_factory_state_assigns_admin_role(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_provider_factory_state_assigns_provider_role(): void
    {
        $user = User::factory()->provider()->create();

        $this->assertTrue($user->hasRole('provider'));
    }

    public function test_customer_factory_state_assigns_customer_role(): void
    {
        $user = User::factory()->customer()->create();

        $this->assertTrue($user->hasRole('customer'));
    }

    // -------------------------------------------------------------------------
    // Role checks (hasRole / getRoleNames)
    // -------------------------------------------------------------------------

    public function test_user_can_only_have_one_role_at_a_time(): void
    {
        $user = User::factory()->customer()->create();

        $this->assertCount(1, $user->getRoleNames());
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_admin_does_not_have_customer_or_provider_role(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertFalse($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('provider'));
    }
}
