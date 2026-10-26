<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Stripe\ApiRequestor;

/**
 * @since TBD
 */
trait UsesFakeStripeApi
{
    /**
     * @var FakeStripeHttpClient
     */
    protected $stripe;

    /**
     * @since TBD
     *
     * @before
     */
    protected function setUpFakeStripeApi(): void
    {
        $this->stripe = new FakeStripeHttpClient();
        ApiRequestor::setHttpClient($this->stripe);
    }

    /**
     * @since TBD
     *
     * @after
     */
    protected function tearDownFakeStripeApi(): void
    {
        ApiRequestor::setHttpClient(null);
    }

    /**
     * @since TBD
     */
    protected function makeAccount(array $overrides = []): AccountDetail
    {
        return AccountDetail::fromArray(
            array_merge(
                [
                    'type' => 'connect',
                    'account_name' => 'Connected account',
                    'account_slug' => 'acct_connected',
                    'account_email' => 'connected@example.com',
                    'account_country' => 'US',
                    'account_id' => 'acct_connected',
                    'live_secret_key' => 'sk_live_connected',
                    'live_publishable_key' => 'pk_live_connected',
                    'test_secret_key' => 'sk_test_connected',
                    'test_publishable_key' => 'pk_test_connected',
                    'statement_descriptor' => 'Give',
                ],
                $overrides
            )
        );
    }

    /**
     * A webhook endpoint as the Stripe API returns it.
     *
     * @since TBD
     */
    protected function endpointBody(array $overrides = []): array
    {
        return array_merge(
            [
                'id' => 'we_123',
                'object' => 'webhook_endpoint',
                'api_version' => '2019-05-16',
                'application' => null,
                'created' => 1792784736,
                'description' => null,
                'enabled_events' => ['payment_intent.succeeded'],
                'livemode' => false,
                'metadata' => [],
                'status' => 'enabled',
                'url' => 'https://example.com/?give-listener=stripe',
            ],
            $overrides
        );
    }

    /**
     * @since TBD
     */
    protected function errorBody(string $type, string $message): array
    {
        return ['error' => ['type' => $type, 'message' => $message]];
    }
}
