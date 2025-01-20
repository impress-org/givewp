<?php

namespace Unit\Framework\PaymentGateways\Webhooks;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassByDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassBySubscriptionStatus;
use Give\Framework\PaymentGateways\Webhooks\WebhookEvents;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class WebhookEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testSetDonationStatus()
    {
        foreach (DonationStatus::values() as $status) {
            // Ignore deprecated status that don't have event handler classes
            if ( ! (new GetEventHandlerClassByDonationStatus())($status)) {
                continue;
            }

            $gatewayId = TestGateway::id();
            $webhookEvents = new WebhookEvents($gatewayId);
            $webhookEvents->deleteAll();
            $webhookEvents->setDonationStatus($status, '123456');
            $events = $webhookEvents->getAll();

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                sprintf('givewp_%s_webhook_event_donation_status_%s', $gatewayId, $status->getValue()),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @unreleased
     */
    public function testSetSubscriptionStatus()
    {
        foreach (SubscriptionStatus::values() as $status) {
            // Ignore deprecated status that don't have event handler classes
            if ( ! (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                continue;
            }

            $gatewayId = TestGateway::id();
            $webhookEvents = new WebhookEvents($gatewayId);
            $webhookEvents->deleteAll();
            $webhookEvents->setSubscriptionStatus($status, '123456');
            $events = $webhookEvents->getAll();

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                sprintf('givewp_%s_webhook_event_subscription_status_%s', $gatewayId, $status->getValue()),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @unreleased
     */
    public function testSetSubscriptionFirstDonation()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->deleteAll();
        $webhookEvents->setSubscriptionFirstDonation('123456');
        $events = $webhookEvents->getAll();

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_first_donation', $gatewayId),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     */
    public function testSetSubscriptionRenewalDonation()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->deleteAll();
        $webhookEvents->setSubscriptionRenewalDonation('abc', '123456');
        $events = $webhookEvents->getAll();

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_renewal_donation', $gatewayId),
            current($events)->get_hook()
        );
    }
}
