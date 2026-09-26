<?php

namespace App\Demo\Transport;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;

/**
 * Points the framework's error bootstrapper back at the calling application
 * after an in-process request has booted its own copy.
 */
final class ExceptionHandlerBinding extends HandleExceptions
{
    public static function rebind(Container $app): void
    {
        if ($app instanceof Application) {
            self::$app = $app;
        }
    }
}
