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
 * @unreleased
 */
class WebhookEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentAbandoned()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentAbandoned('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::ABANDONED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentCancelled()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentCancelled('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::CANCELLED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentCompleted()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentCompleted('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::COMPLETE()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentFailed()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentFailed('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::FAILED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentPending()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentPending('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PENDING()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentPreapproval()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentPreapproval('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PREAPPROVAL()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentProcessing()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentProcessing('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::PROCESSING()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentRefunded()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentRefunded('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::REFUNDED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testPaymentRevoked()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->paymentRevoked('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getDonationStatusHookName($gatewayId, DonationStatus::REVOKED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionActive()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionActive('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::ACTIVE()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionCancelled()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionCancelled('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::CANCELLED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionCompleted()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionCompleted('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::COMPLETED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionExpired()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionExpired('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::EXPIRED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionFailing()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionFailing('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::FAILING()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionPaused()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionPaused('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::PAUSED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionPending()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionPending('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::PENDING()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSubscriptionSuspended()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);

        $this->deleteAll($webhookEvents);

        // Creates the event
        $webhookEvents->subscriptionSuspended('123456');

        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            $this->getSubscriptionStatusHookName($gatewayId, SubscriptionStatus::SUSPENDED()),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSetSubscriptionFirstDonation()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);
        $this->deleteAll($webhookEvents);
        $webhookEvents->setSubscriptionFirstDonation('123456');
        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_first_donation', $gatewayId),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
     *
     * @throws ReflectionException
     */
    public function testSetSubscriptionRenewalDonation()
    {
        $gatewayId = TestGateway::id();
        $webhookEvents = new WebhookEvents($gatewayId);
        $this->deleteAll($webhookEvents);
        $webhookEvents->setSubscriptionRenewalDonation('abc', '123456');
        $events = $this->getAll($webhookEvents);

        $this->assertTrue(count($events) === 1);
        $this->assertEquals(
            sprintf('givewp_%s_webhook_event_subscription_renewal_donation', $gatewayId),
            current($events)->get_hook()
        );
    }

    /**
     * @unreleased
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
            $webhookEvents = new WebhookEvents($gatewayId);

            $this->deleteAll($webhookEvents);

            // Workaround to access and test the setDonationStatus() protected method.
            $reflection = new ReflectionClass($webhookEvents);
            $setDonationStatus = $reflection->getMethod('setDonationStatus');
            $setDonationStatus->setAccessible(true);
            $setDonationStatus->invoke($webhookEvents, $status, '123456');

            $events = $this->getAll($webhookEvents);

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                $this->getDonationStatusHookName($gatewayId, $status),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @unreleased
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
            $webhookEvents = new WebhookEvents($gatewayId);
            $this->deleteAll($webhookEvents);

            // Workaround to access and test the setSubscriptionStatus() protected method.
            $reflection = new ReflectionClass($webhookEvents);
            $setSubscriptionStatus = $reflection->getMethod('setSubscriptionStatus');
            $setSubscriptionStatus->setAccessible(true);
            $setSubscriptionStatus->invoke($webhookEvents, $status, '123456');

            $events = $this->getAll($webhookEvents);

            $this->assertTrue(count($events) === 1);
            $this->assertEquals(
                $this->getSubscriptionStatusHookName($gatewayId, $status),
                current($events)->get_hook()
            );
        }
    }

    /**
     * @unreleased
     *
     * @param string $returnFormat OBJECT, ARRAY_A, or ids.
     *
     * @throws ReflectionException
     */
    private function getAll(WebhookEvents $webhookEvents, string $returnFormat = OBJECT): array
    {
        // Workaround to access and test the getGroup() protected method.
        $reflection = new ReflectionClass($webhookEvents);
        $getGroup = $reflection->getMethod('getGroup');
        $getGroup->setAccessible(true);
        $group = $getGroup->invoke($webhookEvents);


        return AsBackgroundJobs::getActionsByGroup($group, $returnFormat);
    }

    /**
     * @unreleased
     *
     * @return int Total deleted webhook events (action scheduler background jobs).
     *
     * @throws ReflectionException
     */
    private function deleteAll(WebhookEvents $webhookEvents): int
    {
        // Workaround to access and test the getGroup() protected method.
        $reflection = new ReflectionClass($webhookEvents);
        $getGroup = $reflection->getMethod('getGroup');
        $getGroup->setAccessible(true);
        $group = $getGroup->invoke($webhookEvents);

        return AsBackgroundJobs::deleteActionsByGroup($group);
    }

    /**
     * @unreleased
     */
    private function getDonationStatusHookName(string $gatewayId, DonationStatus $status): string
    {
        return sprintf('givewp_%s_webhook_event_donation_status_%s', $gatewayId, $status->getValue());
    }

    /**
     * @unreleased
     */
    private function getSubscriptionStatusHookName(string $gatewayId, SubscriptionStatus $status): string
    {
        return sprintf('givewp_%s_webhook_event_subscription_status_%s', $gatewayId, $status->getValue());
    }
}
