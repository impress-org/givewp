<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\CheckoutException;
use Give\PaymentGateways\Gateways\Stripe\Helpers\CheckoutHelper;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\CheckoutSession;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

/**
 * @since 2.19.0
 */
class CheckoutGateway extends PaymentGateway
{
    use Traits\CheckoutInstructions;
    use Traits\CheckoutModal;
    use Traits\CheckoutRedirect;
    use Traits\HandlePaymentIntentStatus;

    /**
     * @inheritDoc
     * @since 2.19.0
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment( GatewayPaymentData $paymentData )
    {
        $workflow = new Workflow();
        $workflow->bind( $paymentData );

        $workflow->action( new Actions\SaveDonationSummary );
        $workflow->action( new Actions\GetOrCreateStripeCustomer );

        switch (give_stripe_get_checkout_type()) {
            case 'modal':
                return $this->createPaymentModal($workflow);
            case 'redirect':
                return $this->createPaymentRedirect($workflow);
            default:
                throw new CheckoutException('Invalid Checkout Error');
        }
    }

    /**
     * @since 2.19.0
     *
     * @param $workflow
     * @return PaymentProcessing|RedirectOffsite
     * @throws Exceptions\PaymentIntentException
     */
    protected function createPaymentModal($workflow)
    {
        $workflow->action(new Actions\GetPaymentMethodFromRequest);
        $workflow->action(new Actions\CreatePaymentIntent());
        $paymentIntent = $workflow->resolve(PaymentIntent::class);
        return $this->handlePaymentIntentStatus($paymentIntent);
    }

    /**
     * @since 2.19.0
     *
     * @param $workflow
     * @return RedirectOffsite
     */
    protected function createPaymentRedirect($workflow)
    {
        $workflow->action( new Actions\CreateCheckoutSession() );
        $session = $workflow->resolve( CheckoutSession::class );
        $paymentData = $workflow->resolve( GatewayPaymentData::class );
        return new RedirectOffsite(
            $this->getRedirectUrl( $session->id(), give_get_payment_form_id( $paymentData->donationId ) )
        );
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe_checkout';
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

        switch( give_stripe_get_checkout_type() ) {
            case 'modal':
                return $this->getCheckoutInstructions()
                     . $this->getCheckoutModalHTML( $formId, $args );
            case 'redirect':
                return $this->getCheckoutInstructions();
        }
    }
}
