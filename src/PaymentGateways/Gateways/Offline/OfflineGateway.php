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
 */
class OfflineGateway extends PaymentGateway
{
    public static function id(): string
    {
        return 'offline';
    }

    public function getId(): string
    {
        return self::id();
    }

    public function getName(): string
    {
        return __('Offline Donation', 'give');
    }

    public function getPaymentMethodLabel(): string
    {
        return __('Offline Donation', 'give');
    }

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

    public function getLegacyFormFieldMarkup(int $formId): string
    {
        return (new LegacyFormFieldMarkup())($formId, true);
    }

    public function createPayment(Donation $donation, $gatewayData): PaymentPending
    {
        return new PaymentPending();
    }

    public function refundDonation(Donation $donation)
    {
        throw new RuntimeException(
            'Method has not been implemented yet. Please use the legacy method in the meantime.'
        );
    }
}
