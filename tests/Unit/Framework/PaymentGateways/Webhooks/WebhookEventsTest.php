<?php

namespace Unit\Framework\PaymentGateways\Webhooks;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassByDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassBySubscriptionStatus;
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
            // Ignore status that don't have event handler classes
            if ( ! (new GetEventHandlerClassByDonationStatus())($status)) {
                continue;
            }

            TestGateway::webhookEvents()->deleteAll();
            TestGateway::webhookEvents()->setDonationStatus($status, '123456');

            $events = TestGateway::webhookEvents()->getAll();

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                sprintf('givewp_%s_webhook_event_donation_status_%s', TestGateway::id(), $status->getValue()),
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
            // Ignore status that don't have event handler classes
            if ( ! (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                continue;
            }

            TestGateway::webhookEvents()->deleteAll();
            TestGateway::webhookEvents()->setSubscriptionStatus($status, '123456');

            $events = TestGateway::webhookEvents()->getAll();

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                sprintf('givewp_%s_webhook_event_subscription_status_%s', TestGateway::id(), $status->getValue()),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @unreleased
     */
    public function testSetSubscriptionFirstDonation()
    {
        TestGateway::webhookEvents()->deleteAll();
        TestGateway::webhookEvents()->setSubscriptionFirstDonation('123456');

        $events = TestGateway::webhookEvents()->getAll();

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_first_donation', TestGateway::id()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     */
    public function testSetSubscriptionRenewalDonation()
    {
        TestGateway::webhookEvents()->deleteAll();
        TestGateway::webhookEvents()->setSubscriptionRenewalDonation('abc', '123456');

        $events = TestGateway::webhookEvents()->getAll();

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_renewal_donation', TestGateway::id()),
            current($events)->get_hook()
        );
    }
}
