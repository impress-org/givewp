<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Traits\CanSetupStripeApp;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts\EventListener;
use http\Exception\InvalidArgumentException;
use Stripe\Event;

/**
 * @unreleased
 */
abstract class StripeEventListener implements EventListener
{
    use CanSetupStripeApp;

    protected $gatewayTransactionId;

    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        $this->setupDonationTransactionId($event);
        $this->setupStripeApp($this->getFormId());
        $this->verifyEvent($event);
        $this->logEventReceiveTime();

        $this->processEvent($event);
    }

    /**
     * @return void
     */
    protected function setupDonationTransactionId(Event $event)
    {
        $this->gatewayTransactionId = $event->data->object->id;
    }

    /**
     * @unreleased
     *
     * @param string $eventId
     *
     * @throws Exception
     */
    protected function verifyEvent($eventId)
    {
        try {
            Event::retrieve($eventId);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @unreleased
     * @return void
     */
    private function logEventReceiveTime()
    {
        // Update time of webhook received whenever the event is retrieved.
        give_update_option('give_stripe_last_webhook_received_timestamp', time());
    }

    /**
     * @unreleased
     *
     * @return int
     */
    public function getFormId()
    {
        return $this->getDonation()->formId;
    }

    /**
     * @unreleased
     *
     * @return Donation
     */
    public function getDonation()
    {
        if ($donation = Donation::findByGatewayTransactionId($this->gatewayTransactionId)) {
            return $donation;
        }

        throw new InvalidArgumentException('Unable to find donation for the Stripe event.');
    }
}
