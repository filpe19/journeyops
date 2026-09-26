<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Journey Telemetry
    |--------------------------------------------------------------------------
    |
    | Every journey event is persisted to the database (journey_sessions and
    | journey_events tables) and mirrored as one JSON object per line to the
    | file below so it can be inspected with grep / jq.
    |
    */

    'log_path' => env('JOURNEY_LOG_PATH', storage_path('logs/journey.jsonl')),

    // Minutes without activity after which a journey is considered over.
    'idle_minutes' => (int) env('JOURNEY_IDLE_MINUTES', 30),

    // Maps the public ?ref= parameter to a journey source.
    'ref_sources' => [
        'share' => 'event_share',
        'newsletter' => 'email',
        'social' => 'social',
    ],

    // Metadata keys that are never persisted.
    'redacted_keys' => ['password', 'password_confirmation', 'token', '_token', 'cookie', 'remember_token', 'secret'],

];
