<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProviderOnboardingTest extends TestCase
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
    // Helpers
    // -------------------------------------------------------------------------

    private function validApplicationPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Healthy Clinic',
            'bio' => 'We provide top-quality healthcare services.',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'Karachi',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Submitting a provider application
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_submit_provider_application(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/provider/apply', $this->validApplicationPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'application' => [
                    'id',
                    'status',
                    'business_name',
                    'bio',
                    'phone',
                    'applicant' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJsonFragment(['status' => 'pending']);

        $this->assertDatabaseHas('provider_profiles', [
            'user_id' => $user->id,
            'business_name' => 'Healthy Clinic',
            'status' => 'pending',
        ]);
    }

    public function test_unauthenticated_user_cannot_submit_application(): void
    {
        $this->postJson('/api/provider/apply', $this->validApplicationPayload())
            ->assertStatus(401);
    }

    public function test_application_returns_422_when_required_fields_are_missing(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->postJson('/api/provider/apply', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['business_name', 'bio', 'phone']);
    }

    public function test_user_cannot_submit_a_second_application(): void
    {
        $user = User::factory()->customer()->create();
        ProviderProfile::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->postJson('/api/provider/apply', $this->validApplicationPayload());

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You have already submitted a provider application.']);
    }

    // -------------------------------------------------------------------------
    // Viewing own application (GET /provider/me)
    // -------------------------------------------------------------------------

    public function test_applicant_can_view_their_own_application(): void
    {
        $user = User::factory()->customer()->create();
        ProviderProfile::factory()->for($user)->create();

        $this->actingAs($user)
            ->getJson('/api/provider/me')
            ->assertOk()
            ->assertJsonFragment(['status' => 'pending']);
    }

    public function test_provider_me_returns_404_when_no_application_exists(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)
            ->getJson('/api/provider/me')
            ->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_provider_me(): void
    {
        $this->getJson('/api/provider/me')
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Admin — listing applications
    // -------------------------------------------------------------------------

    public function test_admin_can_list_all_provider_applications(): void
    {
        $admin = User::factory()->admin()->create();
        ProviderProfile::factory()->count(3)->create();

        $this->actingAs($admin)
            ->getJson('/api/admin/provider-applications')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_filter_applications_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        ProviderProfile::factory()->pending()->count(2)->create();
        ProviderProfile::factory()->approved()->count(1)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/provider-applications?status=pending');

        $response->assertOk();

        foreach ($response->json('data') as $application) {
            $this->assertEquals('pending', $application['status']);
        }
    }

    public function test_non_admin_cannot_list_provider_applications(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->getJson('/api/admin/provider-applications')
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_list_provider_applications(): void
    {
        $this->getJson('/api/admin/provider-applications')
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Admin — viewing a specific application
    // -------------------------------------------------------------------------

    public function test_admin_can_view_any_provider_application(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = ProviderProfile::factory()->create();

        $this->actingAs($admin)
            ->getJson("/api/admin/provider-applications/{$profile->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $profile->id]);
    }

    public function test_owner_can_view_their_own_application_via_admin_route(): void
    {
        $user = User::factory()->customer()->create();
        $profile = ProviderProfile::factory()->for($user)->create();

        $this->actingAs($user)
            ->getJson("/api/admin/provider-applications/{$profile->id}")
            ->assertOk();
    }

    public function test_other_customer_cannot_view_another_users_application(): void
    {
        $customer = User::factory()->customer()->create();
        $profile = ProviderProfile::factory()->create(); // different user

        $this->actingAs($customer)
            ->getJson("/api/admin/provider-applications/{$profile->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Admin — approving an application
    // -------------------------------------------------------------------------

    public function test_admin_can_approve_a_pending_application(): void
    {
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->customer()->create();
        $profile = ProviderProfile::factory()->for($applicant)->pending()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'approved',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'approved']);

        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
            'status' => 'approved',
        ]);

        // The applicant should now have the 'provider' role
        $this->assertTrue($applicant->fresh()->hasRole('provider'));
        $this->assertFalse($applicant->fresh()->hasRole('customer'));
    }

    public function test_admin_can_reject_a_pending_application_with_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->customer()->create();
        $profile = ProviderProfile::factory()->for($applicant)->pending()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'rejected',
                'rejection_reason' => 'Incomplete documentation.',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'rejected']);

        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
            'status' => 'rejected',
            'rejection_reason' => 'Incomplete documentation.',
        ]);

        // Role should NOT change on rejection
        $this->assertTrue($applicant->fresh()->hasRole('customer'));
    }

    public function test_rejection_requires_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = ProviderProfile::factory()->pending()->create();

        $this->actingAs($admin)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'rejected',
                // rejection_reason intentionally omitted
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('rejection_reason');
    }

    public function test_admin_cannot_review_an_already_approved_application(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = ProviderProfile::factory()->approved()->create();

        $this->actingAs($admin)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'approved',
            ])
            ->assertStatus(403);
    }

    public function test_non_admin_cannot_review_an_application(): void
    {
        $customer = User::factory()->customer()->create();
        $profile = ProviderProfile::factory()->pending()->create();

        $this->actingAs($customer)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'approved',
            ])
            ->assertStatus(403);
    }

    public function test_review_returns_422_when_decision_is_invalid(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = ProviderProfile::factory()->pending()->create();

        $this->actingAs($admin)
            ->postJson("/api/admin/provider-applications/{$profile->id}/review", [
                'decision' => 'maybe',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('decision');
    }
}
