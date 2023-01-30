<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway;

use Give\Donations\Models\Donation;
use Give\Framework\EnqueueScript;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\Support\ValueObjects\Money;
use Stripe\Exception\ApiErrorException;

use function rawurldecode;

/**
 * @since 0.1.0
 */
class NextGenStripeGateway extends PaymentGateway implements NextGenPaymentGatewayInterface
{
    use NextGenStripeRepository;
    use HandleHttpResponses;

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'next-gen-stripe';
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
        return __('Stripe (Next Gen)', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Stripe (Next Gen)', 'give');
    }

    /**
     * @since 0.1.0
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            self::id(),
            'build/nextGenStripeGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
    }

    /**
     * @since 0.1.0
     * @throws ApiErrorException
     */
    public function formSettings(int $formId): array
    {
        $this->setUpStripeAppInfo($formId);

        $stripePublishableKey = $this->getStripePublishableKey($formId);
        $stripeConnectedAccountKey = $this->getStripeConnectedAccountKey($formId);
        $currency = give_get_currency($formId);
        $formDefaultAmount = give_get_default_form_amount($formId);
        $defaultAmount = Money::fromDecimal(!empty($formDefaultAmount) ? $formDefaultAmount : '50', $currency);
        $stripePaymentIntent = $this->generateStripePaymentIntent(
            $stripeConnectedAccountKey,
            $defaultAmount
        );

        return [
            'stripeKey' => $stripePublishableKey,
            'stripeClientSecret' => $stripePaymentIntent->client_secret,
            'stripeConnectedAccountKey' => $stripeConnectedAccountKey,
            'stripePaymentIntentId' => $stripePaymentIntent->id,
        ];
    }

    /**
     * @inheritDoc
     * @throws ApiErrorException
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        /**
         * Get data from client request
         */
        $stripeConnectedAccountKey = $gatewayData['stripeConnectedAccountKey'];
        $stripePaymentIntentId = $gatewayData['stripePaymentIntentId'];
        $redirectReturnUrl = rawurldecode($gatewayData['successUrl']);

        /**
         * Get or create a Stripe customer
         */
        $customer = $this->getOrCreateStripeCustomerFromDonation($stripeConnectedAccountKey, $donation);


        /**
         * Setup Stripe Payment Intent args
         */
        $intentArgs = $this->getPaymentIntentArgsFromDonation($donation, $customer);

        /**
         * Update Payment Intent
         */
        $intent = $this->updateStripePaymentIntent(
            $stripePaymentIntentId,
            $intentArgs
        );

        /**
         * Update Donation Meta
         */
        $this->updateDonationMetaFromPaymentIntent($donation, $intent);

        /**
         * Return response to client
         */
        return new RespondToBrowser([
            'intentStatus' => $intent->status,
            'returnUrl' => $redirectReturnUrl,
        ]);
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
    public function refundDonation(Donation $donation)
    {
        // TODO: Implement refundDonation() method.
    }

    /**
     * @inheritDoc
     */
    public function supportsLegacyForm(): bool
    {
        return false;
    }
}
