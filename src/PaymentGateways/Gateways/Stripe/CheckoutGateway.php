<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\CheckoutException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\CheckoutSession;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

class CheckoutGateway extends PaymentGateway
{
    use Traits\CheckoutInstructions;
    use Traits\CheckoutModal;
    use Traits\HandlePaymentIntentStatus;

    /**
     * @inheritDoc
     * @unreleased
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment( GatewayPaymentData $paymentData )
    {
        $workflow = new Workflow();
        $workflow->bind( $paymentData );

        $workflow->action( new Actions\GetPaymentMethodFromRequest );
        $workflow->action( new Actions\SaveDonationSummary );
        $workflow->action( new Actions\GetOrCreateStripeCustomer );

        switch( give_stripe_get_checkout_type() ) {
            case 'modal':
                $workflow->action( new Actions\CreatePaymentIntent() );
                $paymentIntent = $workflow->resolve( PaymentIntent::class );
                return $this->handlePaymentIntentStatus( $paymentIntent );
            case 'redirect':
                $workflow->action( new Actions\CreateCheckoutSession() );
                $session = $workflow->resolve( CheckoutSession::class );
                return new RedirectOffsite( $session->url );
            default:
                throw new CheckoutException( 'Invalid Checkout Error' );
        }
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe-checkout';
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
        return __('Stripe - Checkout', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Stripe - Checkout', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        Stripe::canShowBillingAddress( $formId, $args );

        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        switch( give_stripe_get_checkout_type() ) {
            case 'modal':
                return $this->getCheckoutModalHTML();
            case 'redirect':
                return $this->getCheckoutInstructions();
        }
    }
}
