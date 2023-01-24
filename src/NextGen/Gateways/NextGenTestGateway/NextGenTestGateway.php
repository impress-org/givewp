<?php

namespace Give\NextGen\Gateways\NextGenTestGateway;

use Give\Donations\Models\Donation;
use Give\Framework\EnqueueScript;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HasRequest;

/**
 * @unreleased
 */
class NextGenTestGateway extends PaymentGateway implements NextGenPaymentGatewayInterface
{
    use HasRequest;

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'test-gateway-next-gen';
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
        return __('Test Gateway (Next Gen)', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Test Gateway (Next Gen)', 'give');
    }

    /**
     * @unreleased
     *
     * @return EnqueueScript
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            self::id(),
            'build/nextGenTestGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData)
    {
        $intent = $gatewayData['testGatewayIntent'];
        $transactionId = "test-gateway-transaction-id-{$intent}-{$donation->id}-";

        return new PaymentComplete($transactionId);
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
        // TODO: Implement refundDonation() method.
    }

    public function formSettings(int $formId): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function supportsLegacyForm(): bool
    {
        return false;
    }
}
