<?php

namespace Give\Controller;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\PayPalCommerce\DataTransferObjects\PayPalWebhookHeaders;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookRegister;

class PayPalWebhooks
{
    /**
     * @since 2.8.0
     *
     * @var MerchantDetails
     */
    private $merchantRepository;

    /**
     * @var Webhooks
     */
    private $webhookRepository;

    /**
     * @since 2.9.0
     *
     * @var WebhookRegister
     */
    private $webhookRegister;

    /**
     * PayPalWebhooks constructor.
     *
     * @since 2.8.0
     *
     * @param MerchantDetails $merchantRepository
     * @param WebhookRegister $register
     * @param Webhooks        $webhookRepository
     */
    public function __construct(
        MerchantDetails $merchantRepository,
        WebhookRegister $register,
        Webhooks $webhookRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->webhookRegister = $register;
        $this->webhookRepository = $webhookRepository;
    }

    /**
     * Handles all webhook event requests. First it verifies that authenticity of the event with
     * PayPal, and then it passes the event along to the appropriate listener to finish.
     *
     * @since 2.8.0
     *
     * @throws Exception
     */
    public function handle()
    {
        if ( ! $this->merchantRepository->accountIsConnected()) {
            return;
        }

        $merchantDetails = $this->merchantRepository->getDetails();

        $event = json_decode(file_get_contents('php://input'), false);

        // If we receive an event that we're not expecting, just ignore it
        if ( ! $this->webhookRegister->hasEventRegistered($event->event_type)) {
            return;
        }

        $payPalHeaders = PayPalWebhookHeaders::fromHeaders(getallheaders());

        if ( ! $this->webhookRepository->verifyEventSignature($merchantDetails->accessToken, $event, $payPalHeaders)) {
            give_record_gateway_error(
                'Failed webhook event verification',
                print_r(
                    [
                        'merchant' => $merchantDetails,
                        'event' => $event,
                        'headers' => getallheaders(),
                    ],
                    true
                )
            );
            throw new Exception('Failed event verification');
        }

        try {
            $this->webhookRegister
                ->getEventHandler($event->event_type)
                ->processEvent($event);
        } catch (Exception $exception) {
            $eventType = empty($event->event_type) ? 'Unknown' : $event->event_type;
            give_record_gateway_error("Error processing webhook: {$eventType}", print_r($event, true));

            throw $exception;
        }
    }
}
