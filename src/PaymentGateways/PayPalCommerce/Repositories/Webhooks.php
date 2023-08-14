<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\DataTransferObjects\PayPalWebhookHeaders;
use Give\PaymentGateways\PayPalCommerce\Models\WebhookConfig;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests\CreateWebhook;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests\DeleteWebhook;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests\UpdateWebhook;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests\VerifyWebhookSignature;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookRegister;
use Give\Route\PayPalWebhooks as WebhooksRoute;

class Webhooks
{
    use HasMode;

    /**
     * @since 2.9.0
     *
     * @var WebhooksRoute
     */
    private $webhookRoute;

    /**
     * @var WebhookRegister
     */
    private $webhooksRegister;

    /**
     * @var PayPalClient
     */
    private $payPalClient;

    /**
     * Webhooks constructor.
     *
     * @since 2.9.0
     *
     * @param WebhooksRoute   $webhookRoute
     * @param PayPalClient    $payPalClient
     * @param WebhookRegister $webhooksRegister
     */
    public function __construct(
        WebhooksRoute $webhookRoute,
        PayPalClient $payPalClient,
        WebhookRegister $webhooksRegister
    ) {
        $this->webhookRoute = $webhookRoute;
        $this->payPalClient = $payPalClient;
        $this->webhooksRegister = $webhooksRegister;
    }

    /**
     * Verifies with PayPal that the given event is securely from PayPal and not some sneaking sneaker
     *
     * @see https://developer.paypal.com/docs/api/webhooks/v1/#verify-webhook-signature
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.9.0
     *
     * @param object               $event The event to verify
     * @param PayPalWebhookHeaders $payPalHeaders The PayPal headers from the request
     *
     * @return bool
     */
    public function verifyEventSignature($event, $payPalHeaders)
    {
        $webhookConfig = $this->getWebhookConfig();

        $requestData = [
            'transmission_id' => $payPalHeaders->transmissionId,
            'transmission_time' => $payPalHeaders->transmissionTime,
            'transmission_sig' => $payPalHeaders->transmissionSig,
            'cert_url' => $payPalHeaders->certUrl,
            'auth_algo' => $payPalHeaders->authAlgo,
            'webhook_id' => $webhookConfig->id,
            'webhook_event' => $event,
        ];

        $response = $this->payPalClient
            ->getHttpClient()
            ->execute(new VerifyWebhookSignature($requestData));

        if (
            $response->statusCode !== 200 ||
            ! property_exists($response->result, 'verification_status')
            || $response->result->verification_status !== 'SUCCESS'
        ) {
            Log::http(
                'Webhook signature failure response',
                [
                    'category' => 'PayPal Commerce Webhook',
                    'response' => $response,
                    'event' => $event,
                    'headers' => getallheaders(),
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Creates a webhook with the given event types registered.
     *
     * @see https://developer.paypal.com/docs/api/webhooks/v1/#webhooks_post
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.9.0
     *
     * @return WebhookConfig
     * @throws Exception
     */
    public function createWebhook(): WebhookConfig
    {
        $events = $this->webhooksRegister->getRegisteredEvents();
        $webhookUrl = $this->webhookRoute->getRouteUrl();

        $request = new CreateWebhook([
            'url' => $webhookUrl,
            'event_types' => array_map(
                static function ($eventType) {
                    return [
                        'name' => $eventType,
                    ];
                },
                $events
            ),
        ]);

        try {
            $response = $this->payPalClient
                ->getHttpClient()
                ->execute($request);

            if (201 !== $response->statusCode || ! property_exists($response->result, 'id')) {
                Log::error(
                    'Create PayPal Commerce Webhook Failure',
                    [
                        'category' => 'PayPal Commerce Webhook',
                        'Response' => $response
                    ]
                );

                throw new Exception('Failed to create webhook');
            }

            return new WebhookConfig($response->result->id, $webhookUrl, $events);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Updates the webhook url and events
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.9.0
     *
     * @param string $webhookId
     *
     * @throws Exception
     */
    public function updateWebhook($webhookId)
    {
        $webhookUrl = $this->webhookRoute->getRouteUrl();
        $requestBody = [
            [
                'op' => 'replace',
                'path' => '/url',
                'value' => $webhookUrl,
            ],
            [
                'op' => 'replace',
                'path' => '/event_types',
                'value' => array_map(
                    static function ($eventType) {
                        return [
                            'name' => $eventType,
                        ];
                    },
                    $this->webhooksRegister->getRegisteredEvents()
                ),
            ],
        ];

        $response = $this->payPalClient
            ->getHttpClient()
            ->execute(new UpdateWebhook($webhookId, $requestBody));

        if (200 !== $response->statusCode || ! property_exists($response->result, 'id')) {
            Log::error(
                'Failed to update PayPal Commerce webhook',
                [
                    'category' => 'PayPal Commerce Webhook',
                    'Webhook ID' => $webhookId,
                    'Response' => $response
                ]
            );

            throw new Exception('Failed to update PayPal Commerce webhook');
        }
    }

    /**
     * Deletes the webhook with the given id.
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.9.0
     *
     * @param string $token
     * @param string $webhookId
     *
     * @return bool Whether or not the deletion was successful
     */
    public function deleteWebhook($token, $webhookId)
    {
        $response = $this->payPalClient
            ->getHttpClient()
            ->execute(new DeleteWebhook($webhookId));

        $code = $response->statusCode;
        $isDeleted = $code >= 200 && $code < 300;

        if (! $isDeleted) {
            Log::error(
                'Failed to delete PayPal Commerce webhook',
                [
                    'category' => 'PayPal Commerce Webhook',
                    'Webhook ID' => $webhookId,
                    'Response' => $response
                ]
            );
        }

        return $isDeleted;
    }

    /**
     * Saves the webhook config in the database
     *
     * @since 2.9.0
     *
     * @param WebhookConfig $config
     */
    public function saveWebhookConfig(WebhookConfig $config)
    {
        update_option($this->getOptionKey(), $config->toArray(), false);
    }

    /**
     * Retrieves the WebhookConfig from the database
     *
     * @since 2.9.0
     *
     * @return WebhookConfig|null
     */
    public function getWebhookConfig()
    {
        $data = get_option($this->getOptionKey(), null);

        return $data ? WebhookConfig::fromArray($data) : null;
    }

    /**
     * Deletes the stored webhook config
     *
     * @since 2.9.0
     */
    public function deleteWebhookConfig()
    {
        delete_option($this->getOptionKey());
    }

    /**
     * Returns the option key for the given mode
     *
     * @return string
     */
    private function getOptionKey()
    {
        return "give_paypal_commerce_{$this->mode}_webhook_config";
    }
}
