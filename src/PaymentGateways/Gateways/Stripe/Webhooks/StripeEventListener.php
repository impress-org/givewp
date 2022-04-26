<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\Gateways\Stripe\Traits\CanSetupStripeApp;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Contracts\EventListener;
use Stripe\Event;

/**
 * @unreleased
 */
abstract class StripeEventListener implements EventListener
{
    use CanSetupStripeApp;

    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        $this->setupStripeApp($this->getFormId($event));
        $this->logEventReceiveTime();

        $this->processEvent($this->getEventFromStripe($event->id));
    }

    /**
     * @unreleased
     *
     * @param string $eventId
     *
     * @return Event
     * @throws Exception
     */
    protected function getEventFromStripe($eventId)
    {
        try {
            return Event::retrieve($eventId);
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
    protected function getFormId(Event $event)
    {
        return $this->getDonation($event)->formId;
    }

    /**
     * @unreleased
     *
     * @return Donation
     */
    protected function getDonation(Event $event)
    {
        if ($donation = Donation::findByGatewayTransactionId($event->data->object->id)) {
            return $donation;
        }

        throw new InvalidArgumentException('Unable to find donation for the Stripe event.');
    }
}
