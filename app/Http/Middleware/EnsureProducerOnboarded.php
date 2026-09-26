<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProducerOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->hasCompletedProducerOnboarding()) {
            return redirect()->route('producer.onboarding');
        }

        return $next($request);
    }
}
