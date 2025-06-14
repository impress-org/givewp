<?php

namespace Unit\Framework\PaymentGateways\Webhooks;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Webhooks\Webhook;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetNotificationUrlShouldThrowException()
    {
        $this->expectException(Exception::class);

        $testGateway = new OfflineGateway(); // This gateway doesn't implement the WebhookNotificationsListener interface
        $webhook = new Webhook($testGateway);
        $webhook->getNotificationUrl();
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetNotificationUrlShouldGenerateValidRoute()
    {
        $testGateway = new TestGateway();
        $webhook = new Webhook($testGateway);
        $notificationUrl = $webhook->getNotificationUrl();
        $expectedUrl = $testGateway->generateGatewayRouteUrl('webhookNotificationsListener');

        $this->assertEquals($expectedUrl, $notificationUrl);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetNotificationUrlShouldGenerateValidRouteWithArgs()
    {
        $testGateway = new TestGateway();
        $webhook = new Webhook($testGateway);

        $args = [
            'notification_type' => 'payments',
            'payment_id' => '123',
        ];

        $notificationUrl = $webhook->getNotificationUrl($args);
        $expectedUrl = $testGateway->generateGatewayRouteUrl('webhookNotificationsListener', $args);

        $this->assertEquals($expectedUrl, $notificationUrl);
    }
}
