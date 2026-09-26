<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Accounts\RegisterAccount;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Journey\JourneyTracker;
use App\Support\PostAuthenticationRedirect;
use App\Support\PurchaseIntent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request, JourneyTracker $journey): View
    {
        $purchase = PurchaseIntent::event($request->session());
        $preselected = $request->query('type') === UserRole::Producer->value
            ? UserRole::Producer->value
            : UserRole::Buyer->value;

        $journey->record('signup_started', [
            'context' => $purchase ? 'checkout' : 'standalone',
        ], $purchase);

        return view('auth.register', [
            'purchase' => $purchase,
            'preselected' => $preselected,
        ]);
    }

    public function store(
        Request $request,
        RegisterAccount $registerAccount,
        JourneyTracker $journey,
        PostAuthenticationRedirect $redirect,
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'account_type' => ['nullable', Rule::in(UserRole::selfService())],
        ]);

        $user = $registerAccount->handle($validated);

        Auth::login($user);
        $request->session()->regenerate();

        $response = $redirect->afterRegistration($user);

        $journey->record('signup_completed', [
            'user_id' => $user->id,
            'target_route' => parse_url($response->getTargetUrl(), PHP_URL_PATH),
        ], PurchaseIntent::event($request->session()));

        return $response;
    }
}
