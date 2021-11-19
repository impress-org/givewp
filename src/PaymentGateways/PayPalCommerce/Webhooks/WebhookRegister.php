<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce\PaymentCaptureCompleted;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce\PaymentCaptureDenied;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce\PaymentCaptureRefunded;

class WebhookRegister
{
    /**
     * Array of the PayPal webhook event handlers. Add-ons can use the registerEventHandler method
     * to add additional events/handlers.
     *
     * Structure: PayPalEventName => EventHandlerClass
     *
     * @since 2.9.0
     *
     * @var string[]
     */
    private $eventHandlers = [
        'PAYMENT.CAPTURE.REFUNDED' => PaymentCaptureRefunded::class,
        'PAYMENT.CAPTURE.COMPLETED' => PaymentCaptureCompleted::class,
        'PAYMENT.CAPTURE.DENIED' => PaymentCaptureDenied::class,
    ];

    /**
     * Use this to register additional events and handlers
     *
     * @since 2.9.0
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
     * @since 2.9.0
     *
     * @param array $handlers = [ 'PAYPAL.EVENT' => EventHandler::class ]
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
     * @since 2.9.0
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
     * @since 2.9.0
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
     * @since 2.9.0
     *
     * @return string[]
     */
    public function getRegisteredEvents()
    {
        return array_keys($this->eventHandlers);
    }
}
