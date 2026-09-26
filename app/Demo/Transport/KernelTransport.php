<?php

namespace App\Demo\Transport;

use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;

/**
 * Dispatches requests through a freshly booted copy of the application,
 * exactly like a web server would, but without opening a socket.
 */
final class KernelTransport implements Transport
{
    public function __construct(private readonly string $baseUrl) {}

    public function send(string $method, string $path, array $params, array $cookies): TransportResponse
    {
        $outer = Container::getInstance();

        /** @var Application $app */
        $app = require base_path('bootstrap/app.php');
        $kernel = $app->make(Kernel::class);

        $request = Request::create(rtrim($this->baseUrl, '/').$path, $method, $params, $cookies, [], [
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_USER_AGENT' => 'JourneyOps-Synthetic/1.0',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        try {
            $response = $kernel->handle($request);
            $kernel->terminate($request, $response);
        } finally {
            restore_error_handler();
            restore_exception_handler();
            ExceptionHandlerBinding::rebind($outer);
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($outer);
            Container::setInstance($outer);
            Model::setConnectionResolver($outer->make('db'));
            Model::setEventDispatcher($outer->make('events'));
        }

        $jar = [];
        foreach ($response->headers->getCookies() as $cookie) {
            // Compare against the application clock, which may be simulated.
            $expires = $cookie->getExpiresTime();
            $cleared = $cookie->getValue() === null || ($expires !== 0 && $expires < Carbon::now()->getTimestamp());
            $jar[$cookie->getName()] = $cleared ? null : $cookie->getValue();
        }

        $app->flush();
        unset($app, $kernel);
        gc_collect_cycles();

        return new TransportResponse(
            $response->getStatusCode(),
            $response->headers->get('Location'),
            (string) $response->getContent(),
            $jar,
            $response->headers->get('X-Journey-Id'),
        );
    }
}
