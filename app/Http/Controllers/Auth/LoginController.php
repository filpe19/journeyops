<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Journey\JourneyTracker;
use App\Support\PostAuthenticationRedirect;
use App\Support\PurchaseIntent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'purchase' => PurchaseIntent::event($request->session()),
        ]);
    }

    public function store(Request $request, JourneyTracker $journey, PostAuthenticationRedirect $redirect): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in a minute.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = $request->user();
        $response = $redirect->afterLogin($user);

        $journey->record('login_completed', [
            'target_route' => parse_url($response->getTargetUrl(), PHP_URL_PATH),
        ], PurchaseIntent::event($request->session()));

        return $response;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
