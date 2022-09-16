<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway;

use Give\Donations\Models\Donation;
use Give\Framework\EnqueueScript;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HasRequest;
use Give\Framework\Support\ValueObjects\Money;
use Stripe\Exception\ApiErrorException;

/**
 * @unreleased
 */
class NextGenStripeGateway extends PaymentGateway implements NextGenPaymentGatewayInterface
{
    use HasRequest;
    use NextGenStripeRepository;

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
        return __('Next Gen Stripe', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Next Gen Stripe', 'give');
    }

    /**
     * @unreleased
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            $this->getId(),
            'build/nextGenStripeGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
    }

    /**
     * @unreleased
     * @throws ApiErrorException
     */
    public function formSettings(int $formId): array
    {
        give_stripe_set_app_info($formId);

        $stripePublishableKey = give_stripe_get_publishable_key($formId);
        $stripeConnectedAccountKey = give_stripe_get_connected_account_id($formId);
        $currency = give_get_currency($formId);
        $formDefaultAmount = give_get_default_form_amount($formId);
        $defaultAmount = Money::fromDecimal(!empty($formDefaultAmount) ? $formDefaultAmount : '50', $currency);
        $stripePaymentIntent = $this->generateStripePaymentIntent(
            $stripeConnectedAccountKey,
            $defaultAmount
        );

        return [
            'successUrl' => give_get_success_page_uri(),
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

        /**
         * Get or create a Stripe customer
         */
        $customer = $this->getOrCreateStripeCustomerFromDonation(
            $stripeConnectedAccountKey,
            $donation
        );

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
            'status' => 200,
            'intentStatus' => $intent->status
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return 'Legacy Stripe Fields Not Supported.';
    }

    /**
     * @inheritDoc
     */
    public function refundDonation(Donation $donation)
    {
        // TODO: Implement refundDonation() method.
    }
}
