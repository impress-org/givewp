<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Gateways\Stripe;
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
    protected function createPaymentModal(Donation $donation)
    {
        $paymentMethod = Call::invoke(Actions\GetPaymentMethodFromRequest::class, $donation);
        $donationSummary = Call::invoke(Actions\SaveDonationSummary::class, $donation);
        $stripeCustomer = Call::invoke(Actions\GetOrCreateStripeCustomer::class, $donation);

        $createIntentAction = new Actions\CreatePaymentIntent([]);

        return $this->handlePaymentIntentStatus(
            $createIntentAction(
                $donation,
                $donationSummary,
                $stripeCustomer,
                $paymentMethod
            ),
            $donation
        );
    }

    use Traits\HandlePaymentIntentStatus;

    /**
     * @inheritDoc
     * @since 2.19.0
     * @throws PaymentGatewayException
     */
    public function createPayment(Donation $donation): GatewayCommand
    {
        switch (give_stripe_get_checkout_type()) {
            case 'modal':
                return $this->createPaymentModal($donation);
            case 'redirect':
                return $this->createPaymentRedirect($donation);
            default:
                throw new CheckoutException('Invalid Checkout Error');
        }
    }

    /**
     * @since 2.19.7 fix argument order of CreateCheckoutSession
     * @since 2.19.0
     */
    protected function createPaymentRedirect(Donation $donation): RedirectOffsite
    {
        $donationSummary = Call::invoke(Actions\SaveDonationSummary::class, $donation);
        $stripeCustomer = Call::invoke(Actions\GetOrCreateStripeCustomer::class, $donation);
        $session = Call::invoke(Actions\CreateCheckoutSession::class, $donation, $donationSummary, $stripeCustomer);

        return new RedirectOffsite(
            $this->getRedirectUrl($session->id(), give_get_payment_form_id($donation->id))
        );
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_checkout';
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
        return __('Stripe - Checkout', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Stripe - Checkout', 'give');
    }

    /**
     * @inheritDoc
     *
     * @return string|void
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        Stripe::canShowBillingAddress($formId, $args);

        switch (give_stripe_get_checkout_type()) {
            case 'modal':
                return $this->getCheckoutInstructions()
                    . $this->getCheckoutModalHTML($formId, $args);
            case 'redirect':
                return $this->getCheckoutInstructions();
        }
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
