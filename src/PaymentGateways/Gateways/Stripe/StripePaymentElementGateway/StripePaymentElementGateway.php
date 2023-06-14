<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway;

use Give\Donations\Models\Donation;
use Give\Framework\EnqueueScript;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects\StripeGatewayData;
use Stripe\Exception\ApiErrorException;

/**
 * @since 0.1.0
 */
class StripePaymentElementGateway extends PaymentGateway implements NextGenPaymentGatewayInterface
{
    use StripePaymentElementRepository;

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_payment_element';
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
        return __('Stripe - Payment Element', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Stripe Payment Element', 'give');
    }

    /**
     * @since 0.1.0
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            self::id(),
            'build/stripePaymentElementGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
    }

    /**
     * @since 0.1.0
     */
    public function formSettings(int $formId): array
    {
        $this->setUpStripeAppInfo($formId);

        $stripePublishableKey = $this->getStripePublishableKey($formId);
        $stripeConnectedAccountKey = $this->getStripeConnectedAccountKey($formId);

        return [
            'stripeKey' => $stripePublishableKey,
            'stripeConnectedAccountId' => $stripeConnectedAccountKey,
        ];
    }

    /**
     * @inheritDoc
     * @throws ApiErrorException
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        /**
         * Initialize the Stripe SDK using Stripe::setAppInfo()
         */
        $this->setUpStripeAppInfo($donation->formId);

        /**
         * Get data from client request
         */
        $stripeGatewayData = StripeGatewayData::fromRequest($gatewayData);

        /**
         * Get or create a Stripe customer
         */
        $customer = $this->getOrCreateStripeCustomerFromDonation(
            $stripeGatewayData->stripeConnectedAccountId,
            $donation
        );


        /**
         * Setup Stripe Payment Intent args
         */
        $intentData = $this->getPaymentIntentDataFromDonation(
            $donation,
            $customer
        );

        /**
         * Generate Payment Intent
         */
        $intent = $this->generateStripePaymentIntent(
            $stripeGatewayData->stripeConnectedAccountId,
            $intentData
        );

        /**
         * Update Donation Meta
         */
        $this->updateDonationMetaFromPaymentIntent($donation, $intent);

        /**
         * Return response to client.
         * 'clientSecret' is required to confirm payment intent on client side.
         * 'returnUrl' is required to redirect user to success page.
         */
        return new RespondToBrowser([
            'clientSecret' => $intent->client_secret,
            'returnUrl' => $stripeGatewayData->successUrl,
            'billingDetails' => [
                'name' => trim("$donation->firstName $donation->lastName"),
                'email' => $donation->email
            ],
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
