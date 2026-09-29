<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewProviderApplicationRequest;
use App\Http\Resources\ProviderProfileResource;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProviderProfileController extends Controller
{
    /**
     * List all provider applications (admin only).
     *
     * Optional ?status= filter: pending | approved | rejected
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ProviderProfile::class);

        $status = request()->query('status');

        $profiles = ProviderProfile::with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return ProviderProfileResource::collection($profiles);
    }

    /**
     * View a specific provider application.
     *
     * Admins can view any; owners can view their own.
     */
    public function show(ProviderProfile $providerProfile): ProviderProfileResource
    {
        $this->authorize('view', $providerProfile);

        return new ProviderProfileResource($providerProfile->load('user'));
    }

    /**
     * Approve or reject a pending application (admin only).
     *
     * On approval the user's role is upgraded to 'provider'.
     * On rejection a reason must be provided.
     */
    public function review(ReviewProviderApplicationRequest $request, ProviderProfile $providerProfile): JsonResponse
    {
        $this->authorize('review', $providerProfile);

        $decision = $request->validated('decision');

        if ($decision === 'approved') {
            $providerProfile->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_at' => now(),
            ]);

            // Upgrade the user's role from customer → provider
            $providerProfile->user->syncRoles(['provider']);

            $message = 'Provider application approved successfully.';
        } else {
            $providerProfile->update([
                'status' => 'rejected',
                'rejection_reason' => $request->validated('rejection_reason'),
                'reviewed_at' => now(),
            ]);

            $message = 'Provider application rejected.';
        }

        return response()->json([
            'message' => $message,
            'application' => new ProviderProfileResource($providerProfile->refresh()->load('user')),
        ]);
    }

    /**
     * View the authenticated provider's own profile.
     */
    public function me(): JsonResponse
    {
        $profile = request()->user()->providerProfile;

        if (! $profile) {
            return response()->json(['message' => 'No provider application found.'], 404);
        }

        return response()->json(new ProviderProfileResource($profile->load('user')));
    }
}
