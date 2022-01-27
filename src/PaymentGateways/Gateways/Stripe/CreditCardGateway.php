<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentIntentException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

/**
 * @unreleased
 */
class CreditCardGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     * @unreleased
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        $workflow = new Workflow();
        $workflow->bind( $paymentData );

        $workflow->action(new Actions\GetPaymentMethodFromRequest);
        $workflow->action(new Actions\SaveDonationSummary);
        $workflow->action(new Actions\GetOrCreateStripeCustomer);
        $workflow->action(new Actions\CreatePaymentIntent);

        return $this->handlePaymentIntentStatus(
            $workflow->resolve( PaymentIntent::class )
        );
    }

    public function handlePaymentIntentStatus( PaymentIntent $paymentIntent )
    {
        switch( $paymentIntent->status() )  {
            case 'requires_action':
                return new RedirectOffsite( $paymentIntent->nextActionRedirectUrl() );
            case 'succeeded':
                return new PaymentProcessing( $paymentIntent->id() );
            default:
                throw new PaymentIntentException( sprintf( __( 'Unhandled payment intent status: %s', 'give' ), $paymentIntent->status() ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId)
    {
        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        // @TODO Migrate field markup from legacy gateway implementation.
    }
}
