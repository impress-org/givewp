<?php

namespace Unit\Framework\PaymentGateways\Webhooks;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassByDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassBySubscriptionStatus;
use Give\Framework\PaymentGateways\Webhooks\WebhookEvents;
use Give\Framework\Support\Facades\ActionScheduler\AsBackgroundJobs;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionClass;
use ReflectionException;

/**
 * @since 4.5.0
 */
class WebhookEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.5.0
     */
    public function testPaymentAbandoned()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationAbandoned('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::ABANDONED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentCancelled()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationCancelled('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::CANCELLED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentCompleted()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationCompleted('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::COMPLETE()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentFailed()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationFailed('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::FAILED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentPending()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationPending('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PENDING()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentPreapproval()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationPreapproval('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PREAPPROVAL()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentProcessing()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationProcessing('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PROCESSING()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentRefunded()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationRefunded('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::REFUNDED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testPaymentRevoked()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->donationRevoked('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::REVOKED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionActive()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionActive('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::ACTIVE()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionCancelled()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionCancelled('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::CANCELLED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionCompleted()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionCompleted('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::COMPLETED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionExpired()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionExpired('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::EXPIRED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionFailing()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionFailing('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::FAILING()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionPaused()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionPaused('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::PAUSED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionPending()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionPending('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::PENDING()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionSuspended()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionSuspended('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::SUSPENDED()),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionFirstDonation()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionFirstDonation('123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_first_donation', $gatewayId),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     */
    public function testSubscriptionRenewalDonation()
    {
        $gatewayId = TestGateway::id();
        $this->deleteAllEvents($gatewayId);

        // Creates a new event
        $webhookEvents = new WebhookEvents($gatewayId);
        $webhookEvents->subscriptionRenewalDonation('abc', '123456');

        $events = $this->getAllEvents($gatewayId);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_renewal_donation', $gatewayId),
            current($events)->get_hook()
        );
    }

    /**
     * @since 4.5.0
     *
     * @throws ReflectionException
     */
    public function testSetDonationStatus()
    {
        foreach (DonationStatus::values() as $status) {
            // Ignore deprecated status that don't have event handler classes
            if ( ! (new GetEventHandlerClassByDonationStatus())($status)) {
                continue;
            }

            $gatewayId = TestGateway::id();
            $this->deleteAllEvents($gatewayId);

            // Workaround to access and test the setDonationStatus() protected method
            $webhookEvents = new WebhookEvents($gatewayId);
            $reflection = new ReflectionClass($webhookEvents);
            $setDonationStatus = $reflection->getMethod('setDonationStatus');
            $setDonationStatus->setAccessible(true);
            $setDonationStatus->invoke($webhookEvents, $status, '123456');

            $events = $this->getAllEvents($gatewayId);

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                $this->getDonationStatusHookName($gatewayId, $status),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @since 4.5.0
     *
     * @throws ReflectionException
     */
    public function testSetSubscriptionStatus()
    {
        foreach (SubscriptionStatus::values() as $status) {
            // Ignore deprecated status that don't have event handler classes
            if ( ! (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                continue;
            }

            $gatewayId = TestGateway::id();
            $this->deleteAllEvents($gatewayId);

            // Workaround to access and test the setSubscriptionStatus() protected method
            $webhookEvents = new WebhookEvents($gatewayId);
            $reflection = new ReflectionClass($webhookEvents);
            $setSubscriptionStatus = $reflection->getMethod('setSubscriptionStatus');
            $setSubscriptionStatus->setAccessible(true);
            $setSubscriptionStatus->invoke($webhookEvents, $status, '123456');

            $events = $this->getAllEvents($gatewayId);

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                $this->getSubscriptionStatusHookName($gatewayId, $status),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @since 4.5.0
     *
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     */
    private function getAllEvents(string $gatewayId): array
    {
        return AsBackgroundJobs::getActionsByGroup($this->getGroup($gatewayId));
    }

    /**
     * @since 4.5.0
     *
     * @return int Total deleted webhook events (action scheduler background jobs).
     */
    private function deleteAllEvents(string $gatewayId): int
    {
        return AsBackgroundJobs::deleteActionsByGroup($this->getGroup($gatewayId));
    }

    private function getGroup(string $gatewayId): string
    {
        return 'givewp-payment-gateway-' . $gatewayId;
    }

    /**
     * @since 4.5.0
     */
    private function getDonationStatusHookName(string $gatewayId, DonationStatus $status): string
    {
        return sprintf('givewp_%s_webhook_event_donation_status_%s', $gatewayId, $status->getValue());
    }

    /**
     * @since 4.5.0
     */
    private function getSubscriptionStatusHookName(string $gatewayId, SubscriptionStatus $status): string
    {
        return sprintf('givewp_%s_webhook_event_subscription_status_%s', $gatewayId, $status->getValue());
    }
}
