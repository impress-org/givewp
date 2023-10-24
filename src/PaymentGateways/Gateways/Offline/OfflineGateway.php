<?php

namespace Give\PaymentGateways\Gateways\Offline;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\Offline\Views\LegacyFormFieldMarkup;

/**
 * The Offline payment gateway, intended to reflect donations that are made offline and will be later confirmed.
 *
 * @since 3.0.0
 */
class OfflineGateway extends PaymentGateway
{
    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'offline';
    }

    /**
     * @since 3.0.0
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @since 3.0.0
     */
    public function getName(): string
    {
        return __('Offline Donation', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Offline Donation', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function formSettings(int $formId): array
    {
        return [
            'markup' => (new LegacyFormFieldMarkup())($formId, false),
        ];
    }

    /**
     * @since 3.0.0
     */
    public function enqueueScript(int $formId)
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/offlineGateway.asset.php');

        wp_enqueue_script(
            $this::id(),
            GIVE_PLUGIN_URL . 'build/offlineGateway.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        Language::setScriptTranslations($this::id());
    }

    /**
     * @since 3.0.0
     */
    public function getLegacyFormFieldMarkup(int $formId): string
    {
        return (new LegacyFormFieldMarkup())($formId, true);
    }

    /**
     * @since 3.0.0
     */
    public function createPayment(Donation $donation, $gatewayData): PaymentPending
    {
        return new PaymentPending();
    }

    /**
     * @since 3.0.0
     */
    public function refundDonation(Donation $donation)
    {
        throw new RuntimeException(
            'Method has not been implemented yet. Please use the legacy method in the meantime.'
        );
    }
}
