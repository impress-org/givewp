<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\ListWebhookEndpoints;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class ListWebhookEndpointsTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItReturnsTheEndpointsFromEveryPage(): void
    {
        $first = $this->endpointBody(['id' => 'we_first']);
        $second = $this->endpointBody(['id' => 'we_second', 'status' => 'disabled', 'metadata' => ['site' => 'a']]);

        $this->stripe->queue(200, [
            'object' => 'list',
            'url' => '/v1/webhook_endpoints',
            'has_more' => true,
            'data' => [$first],
        ]);
        $this->stripe->queue(200, [
            'object' => 'list',
            'url' => '/v1/webhook_endpoints',
            'has_more' => false,
            'data' => [$second],
        ]);

        $endpoints = (new ListWebhookEndpoints())($this->makeAccount(), 'test');

        $this->assertSame([$first['id'], $second['id']], array_column($endpoints, 'id'));
        $this->assertSame($second['status'], $endpoints[1]->status);
        $this->assertSame($second['metadata'], $endpoints[1]->metadata);
        $this->assertSame($first['enabled_events'], $endpoints[0]->enabledEvents);
        $this->assertNull($endpoints[0]->secret);

        $this->assertCount(2, $this->stripe->requests);
        $this->assertSame('get', $this->stripe->requests[0]['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints', $this->stripe->requests[0]['absUrl']);
        $this->assertSame($first['id'], $this->stripe->requests[1]['params']['starting_after']);
        $this->assertSame(
            $this->stripe->header(0, 'Authorization'),
            $this->stripe->header(1, 'Authorization')
        );
    }

    /**
     * @since TBD
     */
    public function testItAuthenticatesWithTheTokenOfTheRequestedMode(): void
    {
        $account = $this->makeAccount();
        $emptyList = ['object' => 'list', 'url' => '/v1/webhook_endpoints', 'has_more' => false, 'data' => []];

        $this->stripe->queue(200, $emptyList);
        $this->stripe->queue(200, $emptyList);

        (new ListWebhookEndpoints())($account, 'test');
        (new ListWebhookEndpoints())($account, 'live');

        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(1, 'Authorization'));
        $this->assertNull($this->stripe->header(0, 'Stripe-Account'));
    }

    /**
     * @since TBD
     */
    public function testItWrapsTheErrorStripeReturns(): void
    {
        $error = $this->errorBody('authentication_error', 'Invalid API Key provided');
        $this->stripe->queue(401, $error);

        try {
            (new ListWebhookEndpoints())($this->makeAccount(), 'test');
            $this->fail('Expected a WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertSame($error['error']['message'], $exception->getMessage());
            $this->assertSame(401, $exception->getHttpStatus());
            $this->assertSame($error['error']['type'], $exception->getErrorType());
        }
    }

    /**
     * @since TBD
     */
    public function testItDoesNotCallStripeWhenTheAccountHasNoTokenForTheMode(): void
    {
        $this->expectException(WebhookEndpointException::class);

        try {
            (new ListWebhookEndpoints())($this->makeAccount(['live_secret_key' => '']), 'live');
        } finally {
            $this->assertSame([], $this->stripe->requests);
        }
    }

    /**
     * @since TBD
     */
    public function testItRefusesATokenThatBelongsToTheOtherMode(): void
    {
        $this->expectException(WebhookEndpointException::class);

        try {
            (new ListWebhookEndpoints())($this->makeAccount(['test_secret_key' => 'sk_live_wrongmode']), 'test');
        } finally {
            $this->assertSame([], $this->stripe->requests);
        }
    }

    /**
     * @since TBD
     */
    public function testItRefusesAModeOtherThanLiveOrTest(): void
    {
        $this->expectException(WebhookEndpointException::class);

        (new ListWebhookEndpoints())($this->makeAccount(), 'sandbox');
    }
}
