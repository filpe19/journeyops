<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Decides where a user lands after signing in or signing up.
 */
class PostAuthenticationRedirect
{
    public function afterLogin(User $user): RedirectResponse
    {
        return redirect()->intended($this->homeFor($user));
    }

    public function afterRegistration(User $user): RedirectResponse
    {
        // New organizers must set up their public profile before anything else.
        if ($user->isProducer() && ! $user->hasCompletedProducerOnboarding()) {
            return redirect()->route('producer.onboarding');
        }

        return redirect()->intended($this->homeFor($user));
    }

    public function homeFor(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => route('ops.dashboard'),
            UserRole::Producer => route('producer.dashboard'),
            default => route('account'),
        };
    }
}
