<?php

namespace Give\Framework\PaymentGateways\Webhooks;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @since 4.5.0
 */
class Webhook
{
    /**
     * @since 4.5.0
     *
     * @var string $webhookNotificationsListener
     */
    private $webhookNotificationsListener = 'webhookNotificationsListener';

    /**
     * @since 4.5.0
     *
     * @var PaymentGateway $paymentGateway
     */
    private $paymentGateway;

    /**
     * @since 4.5.0
     *
     * @var WebhookEvents $webhookEvents
     */
    public $events;

    public function __construct(PaymentGateway &$paymentGateway)
    {
        if ($paymentGateway->canListeningWebhookNotifications()) {
            $paymentGateway->routeMethods[] = $this->webhookNotificationsListener;
        }

        $this->paymentGateway = &$paymentGateway;
        $this->events = new WebhookEvents($paymentGateway::id());
    }

    /**
     * @since 4.5.0
     *
     * @throws Exception
     */
    public function getNotificationUrl(array $args = []): string
    {
        if ( ! $this->paymentGateway->canListeningWebhookNotifications()) {
            throw new Exception('Gateway does not support listening to webhook notifications.');
        }

        return $this->paymentGateway->generateGatewayRouteUrl($this->webhookNotificationsListener, $args);
    }
}
