<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\UpdateWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use InvalidArgumentException;

/**
 * @since 4.20.0
 */
final class UpdateWebhookEndpointTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since 4.20.0
     */
    public function testItSendsOnlyTheChangedFieldsToTheEndpoint(): void
    {
        $account = $this->makeAccount();
        $this->stripe->queue(200, $this->endpointBody(['status' => 'disabled', 'description' => 'Paused']));

        $endpoint = (new UpdateWebhookEndpoint())(
            $account,
            'test',
            'we_123',
            ['disabled' => true, 'description' => 'Paused']
        );

        $this->assertSame('disabled', $endpoint->status);
        $this->assertSame('Paused', $endpoint->description);

        $request = $this->stripe->requests[0];
        $this->assertSame('post', $request['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_123', $request['absUrl']);
        $this->assertSame(['disabled' => 'true', 'description' => 'Paused'], $request['params']);
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
    }

    /**
     * @since 4.20.0
     */
    public function testItRefusesToChangeTheApiVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            (new UpdateWebhookEndpoint())($this->makeAccount(), 'test', 'we_123', ['api_version' => '2020-08-27']);
        } finally {
            $this->assertSame([], $this->stripe->requests);
        }
    }

    /**
     * @since 4.20.0
     */
    public function testItRefusesAnEmptyChangeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new UpdateWebhookEndpoint())($this->makeAccount(), 'test', 'we_123', []);
    }

    /**
     * @since 4.20.0
     */
    public function testItRefusesAnIdThatIsNotAWebhookEndpointId(): void
    {
        $this->expectException(WebhookEndpointException::class);

        try {
            (new UpdateWebhookEndpoint())($this->makeAccount(), 'test', 'we_1/../../charges', ['disabled' => true]);
        } finally {
            $this->assertSame([], $this->stripe->requests);
        }
    }

    /**
     * @since 4.20.0
     */
    public function testItWrapsTheErrorStripeReturns(): void
    {
        $error = $this->errorBody('invalid_request_error', 'No such webhook endpoint: we_missing');
        $this->stripe->queue(404, $error);

        try {
            (new UpdateWebhookEndpoint())($this->makeAccount(), 'test', 'we_missing', ['disabled' => false]);
            $this->fail('Expected a WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertSame($error['error']['message'], $exception->getMessage());
            $this->assertSame(404, $exception->getHttpStatus());
        }
    }
}
