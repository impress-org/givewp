<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

/**
 * A gateway for testing the donation process. No actual payment is processed and only form validation is performed.
 *
 * @since 3.0.0 change to Test Donations and manual id to replace legacy gateway
 * @since 2.18.0
 */
class TestGateway extends PaymentGateway
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
}
