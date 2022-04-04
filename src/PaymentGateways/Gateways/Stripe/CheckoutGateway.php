<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\CheckoutException;

/**
 * @since 2.19.0
 */
class CheckoutGateway extends PaymentGateway
{
    use Traits\CheckoutInstructions;
    use Traits\CheckoutModal;
    use Traits\CheckoutRedirect;

    /**
     * @since 2.19.7 fix handlePaymentIntentStatus not receiving extra param
     * @since 2.19.0
     * @return PaymentProcessing|RedirectOffsite
     * @throws Exceptions\PaymentIntentException
     * @throws InvalidPropertyName
     */
    protected function createPaymentModal( GatewayPaymentData $paymentData )
    {
        $paymentMethod = Call::invoke( Actions\GetPaymentMethodFromRequest::class, $paymentData );
        $donationSummary = Call::invoke( Actions\SaveDonationSummary::class, $paymentData );
        $stripeCustomer = Call::invoke( Actions\GetOrCreateStripeCustomer::class, $paymentData );

        $createIntentAction = new Actions\CreatePaymentIntent([]);

        return $this->handlePaymentIntentStatus(
            $createIntentAction(
                $paymentData,
                $donationSummary,
                $stripeCustomer,
                $paymentMethod
            ),
            $paymentData->donationId
        );
    }

    use Traits\HandlePaymentIntentStatus;

    /**
     * @inheritDoc
     * @since 2.19.0
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment(GatewayPaymentData $paymentData)
    {
        switch (give_stripe_get_checkout_type()) {
            case 'modal':
                return $this->createPaymentModal($paymentData);
            case 'redirect':
                return $this->createPaymentRedirect($paymentData);
            default:
                throw new CheckoutException('Invalid Checkout Error');
        }
    }

    /**
     * @since 2.19.7 fix argument order of CreateCheckoutSession
     * @since 2.19.0
     *
     * @return RedirectOffsite
     */
    protected function createPaymentRedirect(GatewayPaymentData $paymentData)
    {
        $donationSummary = Call::invoke(Actions\SaveDonationSummary::class, $paymentData);
        $stripeCustomer = Call::invoke(Actions\GetOrCreateStripeCustomer::class, $paymentData);
        $session = Call::invoke(Actions\CreateCheckoutSession::class, $paymentData, $donationSummary, $stripeCustomer);

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
