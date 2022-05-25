<?php

namespace Give\NextGen\Gateways\Stripe\NextGenCreditCardGateway;

use Give\Framework\EnqueueScript;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use Stripe\PaymentIntent;

/**
 * @unreleased
 */
class NextGenCreditCardGateway extends CreditCardGateway
{
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
        return __('Next Gen Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Next Gen Stripe - Credit Card', 'give');
    }

     /**
     * @unreleased
     */
    public function enqueueScript(): EnqueueScript
    {
        return new EnqueueScript(
            $this->getId(),
            'build/nextGenCreditCardGateway.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );
    }

    /**
     * @unreleased
     */
    public function formSettings($formId): array
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
            'stripeKey' => $stripePublishableKey,
            'stripeClientSecret' => $stripePaymentIntent->client_secret,
            'stripeConnectedAccountKey' => $stripeConnectedAccountKey,
            'successUrl' => give_get_success_page_uri(),
        ];
    }

    /**
     * Mocking Payment Intent while building out api
     *
     * @unreleased
     */
    private function generateStripePaymentIntent($accountId, Money $amount): PaymentIntent
    {
        return PaymentIntent::create(
            [
                'amount' => $amount->formatToMinorAmount(),
                'currency' => $amount->getCurrency()->getCode(),
                'automatic_payment_methods' => ['enabled' => true],
            ],
            ['stripe_account' => $accountId]
        );
    }
}
