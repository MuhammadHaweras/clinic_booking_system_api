<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    /**
     * Admin can list all pending/all applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can view any application; owner can view their own.
     */
    public function view(User $user, ProviderProfile $providerProfile): bool
    {
        return $user->hasRole('admin') || $user->id === $providerProfile->user_id;
    }

    /**
     * Any authenticated user may submit an application (duplicate check is in controller).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admin may approve or reject an application.
     */
    public function review(User $user, ProviderProfile $providerProfile): bool
    {
        return $user->hasRole('admin') && $providerProfile->isPending();
    }
}
