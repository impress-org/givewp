<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support;

use RuntimeException;
use Stripe\HttpClient\ClientInterface;
use Throwable;

/**
 * Replaces the Stripe SDK's HTTP transport so the real SDK code runs without network access.
 *
 * @since TBD
 */
final class FakeStripeHttpClient implements ClientInterface
{
    /**
     * @var array<int, array{method: string, absUrl: string, headers: string[], params: array}>
     */
    public $requests = [];

    /**
     * @var array<int, array{string, int, array}|Throwable>
     */
    private $responses = [];

    /**
     * @since TBD
     */
    public function queue(int $status, array $body): void
    {
        $this->responses[] = [json_encode($body), $status, []];
    }

    /**
     * Makes the next request throw, the way the SDK's cURL client throws ApiConnectionException when Stripe
     * cannot be reached.
     *
     * @since TBD
     */
    public function queueException(Throwable $exception): void
    {
        $this->responses[] = $exception;
    }

    /**
     * @since TBD
     */
    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $this->requests[] = [
            'method' => strtolower($method),
            'absUrl' => $absUrl,
            'headers' => $headers,
            'params' => $params,
        ];

        if (!$this->responses) {
            throw new RuntimeException("No fake Stripe response queued for $method $absUrl");
        }

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response;
    }

    /**
     * Returns the value of a request header, or null when the request did not send it.
     *
     * @since TBD
     */
    public function header(int $requestIndex, string $name): ?string
    {
        foreach ($this->requests[$requestIndex]['headers'] as $header) {
            if (0 === stripos($header, $name . ':')) {
                return trim(substr($header, strlen($name) + 1));
            }
        }

        return null;
    }
}
