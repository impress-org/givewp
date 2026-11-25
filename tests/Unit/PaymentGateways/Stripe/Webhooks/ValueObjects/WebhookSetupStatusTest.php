<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\ValueObjects;

use Give\PaymentGateways\Stripe\Webhooks\Actions\ListWebhookEndpoints;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use RuntimeException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;

/**
 * @since 4.20.0
 */
final class WebhookSetupStatusTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * RateLimitException extends InvalidRequestException, so the rate limit check has to come first.
     *
     * @since 4.20.0
     */
    public function testItClassifiesA429AsRateLimitedRatherThanAnInvalidRequest(): void
    {
        $this->stripe->queue(429, $this->errorBody('invalid_request_error', 'Too many requests.'));

        $exception = $this->listFailure();

        $this->assertInstanceOf(RateLimitException::class, $exception->getPrevious());
        $this->assertInstanceOf(InvalidRequestException::class, $exception->getPrevious());
        $this->assertTrue(WebhookSetupStatus::fromException($exception)->isRateLimited());
    }

    /**
     * @since 4.20.0
     */
    public function testItClassifiesANetworkFailureAsAConnectionProblem(): void
    {
        $this->stripe->queueException(ApiConnectionException::factory('Could not connect to Stripe.'));

        $exception = $this->listFailure();

        $this->assertNull($exception->getHttpStatus());
        $this->assertTrue(WebhookSetupStatus::fromException($exception)->isConnection());
    }

    /**
     * @since 4.20.0
     */
    public function testItClassifiesA400AsAnInvalidRequest(): void
    {
        $this->stripe->queue(400, $this->errorBody('invalid_request_error', 'Invalid URL.'));

        $this->assertTrue(WebhookSetupStatus::fromException($this->listFailure())->isInvalidRequest());
    }

    /**
     * @since 4.20.0
     */
    public function testItClassifiesA500AsUnknown(): void
    {
        $this->stripe->queue(500, $this->errorBody('api_error', 'Something went wrong on Stripe.'));

        $this->assertTrue(WebhookSetupStatus::fromException($this->listFailure())->isUnknown());
    }

    /**
     * @since 4.20.0
     */
    public function testItClassifiesAnExceptionFromOutsideStripeAsUnknown(): void
    {
        $this->assertTrue(WebhookSetupStatus::fromException(new RuntimeException('Boom'))->isUnknown());
    }

    /**
     * @since 4.20.0
     *
     * @dataProvider successProvider
     */
    public function testItCountsOnlyAWorkingEndpointAsSuccessful(WebhookSetupStatus $status, bool $expected): void
    {
        $this->assertSame($expected, $status->isSuccessful());
    }

    /**
     * @since 4.20.0
     */
    public function successProvider(): array
    {
        $successful = [WebhookSetupStatus::FOUND, WebhookSetupStatus::CREATED, WebhookSetupStatus::UPDATED];
        $cases = [];

        foreach (WebhookSetupStatus::toArray() as $value) {
            $cases[$value] = [new WebhookSetupStatus($value), in_array($value, $successful, true)];
        }

        return $cases;
    }

    /**
     * @since 4.20.0
     */
    private function listFailure(): WebhookEndpointException
    {
        try {
            (new ListWebhookEndpoints())($this->makeAccount(), 'test');
        } catch (WebhookEndpointException $exception) {
            return $exception;
        }

        $this->fail('Expected a WebhookEndpointException.');
    }
}
