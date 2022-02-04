<?php

namespace Give\PaymentGateways\PayPalStandard\Gateways\Webhooks;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\PayPalStandard\Gateways\Webhooks\Listeners\EventListener;
use Give\PaymentGateways\PayPalStandard\Gateways\Webhooks\Listeners\PaymentUpdated;

class WebhookRegister
{
    /**
     * Array of the PayPal webhook event handlers. Add-ons can use the registerEventHandler method
     * to add additional events/handlers.
     *
     * Structure: PayPalTractionType => EventHandlerClass
     *
     * @unreleased
     *
     * @var string[]
     */
    private $eventHandlers = [
        'web_accept' => PaymentUpdated::class,
        'cart' => PaymentUpdated::class,
    ];

    /**
     * Use this to register additional events and handlers
     *
     * @unreleased
     *
     * @param string $payPalEvent PayPal event to listen for, i.e. CHECKOUT.ORDER.APPROVED
     * @param string $eventHandler The FQCN of the event handler
     *
     * @return $this
     */
    public function registerEventHandler($payPalEvent, $eventHandler)
    {
        if (isset($this->eventHandlers[$payPalEvent])) {
            throw new InvalidArgumentException('Cannot register an already registered event');
        }

        if ( ! is_subclass_of($eventHandler, EventListener::class)) {
            throw new InvalidArgumentException('Listener must be a subclass of ' . EventListener::class);
        }

        $this->eventHandlers[$payPalEvent] = $eventHandler;

        return $this;
    }

    /**
     * Registers multiple event handlers using an array where the key is the
     *
     * @unreleased
     *
     * @param array $handlers = [ 'web_accept' => EventHandler::class ]
     *                          https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/#link-ipntransactiontypes
     */
    public function registerEventHandlers(array $handlers)
    {
        foreach ($handlers as $event => $handler) {
            $this->registerEventHandler($event, $handler);
        }
    }

    /**
     * Returns Event Listener instance for given event
     *
     * @unreleased
     *
     * @param string $event
     *
     * @return EventListener
     */
    public function getEventHandler($event)
    {
        return give($this->eventHandlers[$event]);
    }

    /**
     * Checks whether the given event is registered
     *
     * @unreleased
     *
     * @param string $event
     *
     * @return bool
     */
    public function hasEventRegistered($event)
    {
        return isset($this->eventHandlers[$event]);
    }

    /**
     * Returns an array of the registered events
     *
     * @unreleased
     *
     * @return string[]
     */
    public function getRegisteredEvents()
    {
        return array_keys($this->eventHandlers);
    }
}
