<?php

namespace App\Demo\Transport;

final class TransportResponse
{
    /**
     * @param  array<string, string|null>  $cookies  null means the cookie was cleared
     */
    public function __construct(
        public readonly int $status,
        public readonly ?string $location,
        public readonly string $body,
        public readonly array $cookies,
        public readonly ?string $journeyId,
    ) {}

    public function isRedirect(): bool
    {
        return $this->status >= 300 && $this->status < 400 && $this->location !== null;
    }
}
