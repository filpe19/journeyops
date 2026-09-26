<?php

namespace App\Demo\Transport;

use Illuminate\Support\Facades\Http;

/**
 * Talks to a running instance of the application over real HTTP.
 */
final class HttpTransport implements Transport
{
    public function __construct(private readonly string $baseUrl) {}

    public function send(string $method, string $path, array $params, array $cookies): TransportResponse
    {
        $cookieHeader = collect($cookies)->map(fn ($v, $k) => $k.'='.rawurlencode((string) $v))->implode('; ');

        $pending = Http::withOptions(['allow_redirects' => false])
            ->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml',
                'User-Agent' => 'JourneyOps-Synthetic/1.0',
                'Cookie' => $cookieHeader,
            ])
            ->timeout(15);

        $url = rtrim($this->baseUrl, '/').$path;
        $response = strtoupper($method) === 'GET'
            ? $pending->get($url)
            : $pending->asForm()->send($method, $url, ['form_params' => $params]);

        $jar = [];
        foreach ($response->toPsrResponse()->getHeader('Set-Cookie') as $header) {
            [$pair] = explode(';', $header, 2);
            [$name, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $cleared = stripos($header, 'expires=Thu, 01 Jan 1970') !== false || stripos($header, 'Max-Age=0') !== false;
            $jar[trim($name)] = $cleared ? null : rawurldecode($value);
        }

        return new TransportResponse(
            $response->status(),
            $response->header('Location') ?: null,
            $response->body(),
            $jar,
            $response->header('X-Journey-Id') ?: null,
        );
    }
}
