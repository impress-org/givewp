<?php

namespace Give\Framework\PaymentGateways\Webhooks;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * @unreleased
 */
class Webhook
{
    /**
     * @unreleased
     *
     * @var string $webhookNotificationsListener
     */
    private $webhookNotificationsListener = 'webhookNotificationsListener';

    /**
     * @unreleased
     *
     * @var PaymentGateway $paymentGateway
     */
    private $paymentGateway;

    /**
     * @unreleased
     *
     * @var WebhookEvents $webhookEvents
     */
    public $events;

    public function __construct(PaymentGateway &$paymentGateway)
    {
        $paymentGateway->routeMethods[] = $this->webhookNotificationsListener;
        $this->paymentGateway = &$paymentGateway;
        $this->events = new WebhookEvents($paymentGateway::id());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function getNotificationUrl(array $args = []): string
    {
        if ( ! $this->paymentGateway->canListeningWebhookNotifications()) {
            throw new Exception('Gateway does not support listening webhook notifications.');
        }

        return $this->paymentGateway->generateGatewayRouteUrl($this->webhookNotificationsListener, $args);
    }
}
