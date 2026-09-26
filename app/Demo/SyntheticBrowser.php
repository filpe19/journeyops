<?php

namespace App\Demo;

use App\Demo\Transport\Transport;
use App\Demo\Transport\TransportResponse;
use Dom\Element;
use Dom\HTMLDocument;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Minimal headless browser: keeps cookies, follows redirects, clicks links
 * and submits forms with the same defaults a real browser would send.
 */
class SyntheticBrowser
{
    /** @var array<string, string> */
    private array $cookies = [];

    private ?TransportResponse $response = null;

    private string $path = '/';

    /** @var list<array{method: string, path: string, status: int}> */
    private array $history = [];

    /** @var list<string> */
    private array $journeyIds = [];

    public function __construct(
        private readonly Transport $transport,
        private ?Carbon $clock = null,
    ) {}

    public function visit(string $path): static
    {
        return $this->request('GET', $path);
    }

    public function click(string $linkText): static
    {
        foreach ($this->document()->querySelectorAll('a[href]') as $link) {
            if ($this->text($link) === $linkText || str_contains($this->text($link), $linkText)) {
                return $this->visit($this->relative($link->getAttribute('href')));
            }
        }

        throw new RuntimeException("Link [{$linkText}] not found on {$this->path}");
    }

    public function canPress(string $buttonText): bool
    {
        return $this->findButton($buttonText) !== null;
    }

    public function canSee(string $text): bool
    {
        return str_contains($this->text($this->document()->body), $text);
    }

    /**
     * @param  array<string, string>  $fields
     */
    public function press(string $buttonText, array $fields = []): static
    {
        $button = $this->findButton($buttonText)
            ?? throw new RuntimeException("Button [{$buttonText}] not found on {$this->path}");

        $form = $button->closest('form') ?? throw new RuntimeException("Button [{$buttonText}] is not inside a form");

        $params = array_merge($this->formDefaults($form), $fields);
        $method = strtoupper($form->getAttribute('method') ?: 'GET');
        $action = $this->relative($form->getAttribute('action') ?: $this->path);

        return $this->request($method, $action, $params);
    }

    public function wait(int $seconds): static
    {
        $this->clock = $this->clock?->copy()->addSeconds($seconds);

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function status(): int
    {
        return $this->response?->status ?? 0;
    }

    public function html(): string
    {
        return $this->response?->body ?? '';
    }

    /**
     * @return list<array{method: string, path: string, status: int}>
     */
    public function history(): array
    {
        return $this->history;
    }

    /**
     * @return list<string>
     */
    public function journeyIds(): array
    {
        return $this->journeyIds;
    }

    private function request(string $method, string $path, array $params = []): static
    {
        for ($hops = 0; $hops < 10; $hops++) {
            $response = $this->dispatch($method, $path, $params);
            $this->history[] = ['method' => $method, 'path' => $path, 'status' => $response->status];

            if (! $response->isRedirect()) {
                $this->response = $response;
                $this->path = $path;

                if ($response->status >= 500) {
                    throw new RuntimeException("Server error {$response->status} on {$method} {$path}");
                }

                return $this;
            }

            $method = 'GET';
            $params = [];
            $path = $this->relative($response->location);
        }

        throw new RuntimeException('Too many redirects');
    }

    private function dispatch(string $method, string $path, array $params): TransportResponse
    {
        $previous = Carbon::getTestNow();

        if ($this->clock !== null) {
            Carbon::setTestNow($this->clock);
        }

        try {
            $response = $this->transport->send($method, $path, $params, $this->cookies);
        } finally {
            Carbon::setTestNow($previous);
        }

        // Each request takes a few simulated seconds of "think time".
        $this->clock = $this->clock?->copy()->addSeconds(7);

        foreach ($response->cookies as $name => $value) {
            if ($value === null) {
                unset($this->cookies[$name]);
            } else {
                $this->cookies[$name] = $value;
            }
        }

        if ($response->journeyId !== null && end($this->journeyIds) !== $response->journeyId) {
            $this->journeyIds[] = $response->journeyId;
        }

        return $response;
    }

    private function document(): HTMLDocument
    {
        return HTMLDocument::createFromString($this->html() ?: '<html><body></body></html>', LIBXML_NOERROR);
    }

    private function findButton(string $text): ?Element
    {
        foreach ($this->document()->querySelectorAll('button, input[type=submit]') as $button) {
            $label = $button->tagName === 'INPUT' ? (string) $button->getAttribute('value') : $this->text($button);
            $type = strtolower($button->getAttribute('type') ?? 'submit');

            if ($type === 'submit' && str_starts_with($label, $text)) {
                return $button;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function formDefaults(Element $form): array
    {
        $values = [];

        foreach ($form->querySelectorAll('input[name], select[name], textarea[name]') as $field) {
            $name = $field->getAttribute('name');

            if ($field->tagName === 'SELECT') {
                $option = $field->querySelector('option[selected]') ?? $field->querySelector('option');
                $values[$name] = $option?->getAttribute('value') ?? '';

                continue;
            }

            if ($field->tagName === 'TEXTAREA') {
                $values[$name] = $field->textContent;

                continue;
            }

            $type = strtolower($field->getAttribute('type') ?? 'text');

            if (in_array($type, ['submit', 'button', 'file', 'image', 'reset'], true)) {
                continue;
            }

            if (in_array($type, ['checkbox', 'radio'], true) && ! $field->hasAttribute('checked')) {
                continue;
            }

            $values[$name] = (string) $field->getAttribute('value');
        }

        return $values;
    }

    private function relative(string $url): string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';

        return isset($parts['query']) ? $path.'?'.$parts['query'] : $path;
    }

    private function text(?Element $element): string
    {
        return trim(preg_replace('/\s+/', ' ', $element?->textContent ?? ''));
    }
}
