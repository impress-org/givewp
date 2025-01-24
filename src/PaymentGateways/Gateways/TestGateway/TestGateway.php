<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Contracts\WebhookNotificationsListener;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

/**
 * A gateway for testing the donation process. No actual payment is processed and only form validation is performed.
 *
 * @unreleased Implements the WebhookNotificationsListener interface
 * @since 3.0.0 change to Test Donations and manual id to replace legacy gateway
 * @since 2.18.0
 */
class TestGateway extends PaymentGateway implements WebhookNotificationsListener
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'manual';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('Test Donation', 'give');
    }

    /**
     * @since 2.32.0 updated to enqueue script
     * @since 2.30.0
     */
    public function enqueueScript(int $formId)
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/testGateway.asset.php');

        wp_enqueue_script(
            $this::id(),
            GIVE_PLUGIN_URL . 'build/testGateway.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        Language::setScriptTranslations($this::id());
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Donation', 'give');
    }

    /**
     * @since 2.18.0
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        if (FormUtils::isLegacyForm($formId)) {
            return '';
        }

        /** @var LegacyFormFieldMarkup $legacyFormFieldMarkup */
        $legacyFormFieldMarkup = give(LegacyFormFieldMarkup::class);

        return $legacyFormFieldMarkup();
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        $intent = $gatewayData['testGatewayIntent'] ?? 'test-gateway-intent';

        return new PaymentComplete("test-gateway-transaction-id-{$intent}-$donation->id");
    }

    /**
     * @since 2.29.0 Return PaymentRefunded instead of a bool value
     * @since      2.20.0
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        return new PaymentRefunded();
    }

    /**
     * This method implementation is a sample that demonstrate how we can handle webhook notifications
     *
     * @unreleased
     */
    public function webhookNotificationsListener()
    {
        try {
            $webhookNotification = give_clean($_REQUEST);

            /**
             * Allow developers to handle the webhook notification.
             *
             * @unreleased
             *
             * @param array $webhookNotification
             */
            do_action('givewp_' . $this::id() . '_webhook_notification_handler', $webhookNotification);

            // We will handle recurring donations in a separate submodule.
            if (isset($webhookNotification['gatewayRecurringPayment']) && $webhookNotification['gatewayRecurringPayment']) {
                return;
            }

            if ( ! isset($webhookNotification['gatewayPaymentStatus']) && ! isset($webhookNotification['gatewayPaymentId'])) {
                return;
            }

            switch (strtolower($webhookNotification['gatewayPaymentStatus'])) {
                case 'complete':
                    $this->webhook->events->paymentCompleted($webhookNotification['gatewayPaymentId']);
                    break;
                case 'failed':
                    $this->webhook->events->paymentFailed($webhookNotification['gatewayPaymentId']);
                    break;
                case 'cancelled':
                    $this->webhook->events->paymentCancelled($webhookNotification['gatewayPaymentId']);
                    break;
                case 'refunded':
                    $this->webhook->events->paymentRefunded($webhookNotification['gatewayPaymentId']);
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            esc_html_e('Webhook Notification failed.', 'give');
            PaymentGatewayLog::error(
                'Webhook Notification failed. Error: ' . $e->getMessage()
            );
        }

        exit();
    }
}
