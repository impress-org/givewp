<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class TestGateway
 * @since 2.18.0
 */
class TestGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'test-gateway';
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
        return __('Test Gateway', 'give');
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
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway', 'give');
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
     * @inheritDoc
     *
     * @since 2.23.0
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData
    ): GatewayCommand {
        return new SubscriptionComplete(
            "test-gateway-transaction-id-$donation->id",
            "test-gateway-subscription-id-$subscription->id"
        );
    }

    /**
     * @since 2.23.0
     *
     * @inheritDoc
     * @throws Exception
     */
    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->status = SubscriptionStatus::CANCELLED();
        $subscription->save();
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
