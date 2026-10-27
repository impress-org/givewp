<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\CheckWebhookEndpointHealth;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookHealthStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class CheckWebhookEndpointHealthTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     *
     * @dataProvider endpointProvider
     */
    public function testItReportsTheStateOfTheEndpointWithoutChangingIt(array $endpoints, string $expected): void
    {
        $this->stripe->queue(200, $this->listBody($endpoints));

        $status = give(CheckWebhookEndpointHealth::class)($this->makeAccount(), 'test');

        $this->assertSame($expected, $status->getValue());
        $this->assertSame(['get'], array_column($this->stripe->requests, 'method'));
    }

    /**
     * @since TBD
     */
    public function endpointProvider(): array
    {
        $site = [
            'url' => home_url('/?give-listener=stripe'),
            'enabled_events' => WebhookTarget::REQUIRED_EVENTS,
        ];

        return [
            'healthy' => [[$this->endpointBody($site)], WebhookHealthStatus::HEALTHY],
            'missing' => [[$this->endpointBody()], WebhookHealthStatus::MISSING],
            'disabled' => [[$this->endpointBody(['status' => 'disabled'] + $site)], WebhookHealthStatus::DISABLED],
            'missing events' => [
                [$this->endpointBody(['enabled_events' => ['charge.refunded']] + $site)],
                WebhookHealthStatus::MISSING_EVENTS,
            ],
        ];
    }
}
