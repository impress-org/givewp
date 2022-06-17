<?php

namespace Give\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\Gateways\TestGateway\Views\LegacyFormFieldMarkup;

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
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway', 'give');
    }

    /**
     * @inheritDoc
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
    public function createPayment(Donation $donation, $gatewayData = null): GatewayCommand
    {
        $transactionId = "test-gateway-transaction-id-{$donation->id}";

        return new PaymentComplete($transactionId);
    }

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }
}
