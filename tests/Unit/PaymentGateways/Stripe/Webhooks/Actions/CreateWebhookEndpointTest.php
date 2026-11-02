<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\CreateWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class CreateWebhookEndpointTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItCreatesTheEndpointOnTheConnectedAccountAndReturnsTheSecret(): void
    {
        $url = 'https://example.com/?give-listener=stripe';
        $events = ['payment_intent.succeeded', 'charge.refunded'];
        $metadata = ['site' => 'example.com'];
        $account = $this->makeAccount();

        $this->stripe->queue(200, $this->endpointBody([
            'id' => 'we_new',
            'url' => $url,
            'enabled_events' => $events,
            'description' => 'GiveWP',
            'metadata' => $metadata,
            'secret' => 'whsec_created',
        ]));

        $endpoint = (new CreateWebhookEndpoint())($account, 'test', $url, $events, '2019-05-16', 'GiveWP', $metadata);

        $this->assertSame('we_new', $endpoint->id);
        $this->assertSame('whsec_created', $endpoint->secret);
        $this->assertSame($events, $endpoint->enabledEvents);
        $this->assertSame($metadata, $endpoint->metadata);

        $request = $this->stripe->requests[0];
        $this->assertSame('post', $request['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints', $request['absUrl']);
        $this->assertSame(
            [
                'url' => $url,
                'enabled_events' => $events,
                'api_version' => '2019-05-16',
                'description' => 'GiveWP',
                'metadata' => $metadata,
            ],
            $request['params']
        );
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertNull($this->stripe->header(0, 'Stripe-Account'));
    }

    /**
     * @since TBD
     */
    public function testItOmitsTheOptionalFieldsThatWereNotGiven(): void
    {
        $this->stripe->queue(200, $this->endpointBody());

        (new CreateWebhookEndpoint())(
            $this->makeAccount(),
            'test',
            'https://example.com/?give-listener=stripe',
            ['*'],
            '2019-05-16'
        );

        $this->assertSame(['url', 'enabled_events', 'api_version'], array_keys($this->stripe->requests[0]['params']));
    }

    /**
     * @since TBD
     */
    public function testItWrapsTheErrorStripeReturns(): void
    {
        $error = $this->errorBody('invalid_request_error', 'Invalid URL: must be HTTPS in live mode.');
        $this->stripe->queue(400, $error);

        try {
            (new CreateWebhookEndpoint())(
                $this->makeAccount(),
                'live',
                'http://example.com',
                ['*'],
                '2019-05-16'
            );
            $this->fail('Expected a WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertSame($error['error']['message'], $exception->getMessage());
            $this->assertSame(400, $exception->getHttpStatus());
            $this->assertSame($error['error']['type'], $exception->getErrorType());
        }
    }
}
