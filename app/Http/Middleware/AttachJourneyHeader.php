<?php

namespace App\Http\Middleware;

use App\Journey\JourneyTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exposes the active journey id as a response header so support and
 * tooling can correlate a browser session with its telemetry.
 */
class AttachJourneyHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->hasSession()) {
            $uuid = $request->session()->get(JourneyTracker::SESSION_JOURNEY)
                ?? $request->attributes->get('journey.ended_uuid');

            if ($uuid) {
                $response->headers->set('X-Journey-Id', $uuid);
            }
        }

        return $response;
    }
}
