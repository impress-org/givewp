<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Webhooks\Contracts\EventListener;
use Give\PaymentGateways\Gateways\Stripe\Traits\CanSetupStripeApp;
use Stripe\ApiResource;
use Stripe\Event;

/**
 * @unreleased
 */
abstract class StripeEventListener implements EventListener
{
    use CanSetupStripeApp;

    /**
     * @param Event $event
     *
     * @return void
     * @throws Exception
     */
    public function __invoke($event)
    {
        /* @var ApiResource $eventDataObject */
        $eventDataObject = $event->data->object;

        $this->setupStripeApp($this->getFormId($eventDataObject));
        $this->verifyEvent($event);

        $this->processEvent($eventDataObject);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function verifyEvent($eventId)
    {
        try {
            Event::retrieve($eventId);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @unreleased
     *
     * @param ApiResource $stripeEvent
     *
     * @return int
     */
    abstract public function getFormId($stripeEvent);
}
