<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderApplicationRequest;
use App\Http\Resources\ProviderProfileResource;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;

class ProviderApplicationController extends Controller
{
    /**
     * Submit a provider onboarding application.
     *
     * Any authenticated user who does not already have a profile may apply.
     * Customers who are approved will have their role upgraded to 'provider'.
     */
    public function store(StoreProviderApplicationRequest $request): JsonResponse
    {
        $this->authorize('create', ProviderProfile::class);

        if ($request->user()->providerProfile()->exists()) {
            return response()->json([
                'message' => 'You have already submitted a provider application.',
            ], 422);
        }

        $profile = $request->user()->providerProfile()->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Your provider application has been submitted and is pending review.',
            'application' => new ProviderProfileResource($profile->load('user')),
        ], 201);
    }
}
