<?php

namespace App\Demo;

use App\Demo\Transport\HttpTransport;
use App\Demo\Transport\KernelTransport;
use Illuminate\Support\Carbon;

class BrowserFactory
{
    /**
     * @param  string|null  $baseUrl  When given, requests go over HTTP to a running server.
     *                                Otherwise they are dispatched in-process.
     */
    public function make(?string $baseUrl = null, ?Carbon $startAt = null): SyntheticBrowser
    {
        $transport = $baseUrl !== null
            ? new HttpTransport($baseUrl)
            : new KernelTransport(config('app.url'));

        return new SyntheticBrowser($transport, $baseUrl === null ? $startAt : null);
    }
}
