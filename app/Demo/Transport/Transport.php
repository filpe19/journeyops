<?php

namespace App\Demo\Transport;

interface Transport
{
    /**
     * @param  array<string, string>  $params
     * @param  array<string, string>  $cookies
     */
    public function send(string $method, string $path, array $params, array $cookies): TransportResponse;
}
