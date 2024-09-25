<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Call;
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\CheckoutException;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\CheckoutTypeException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

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
     *
     * @param  array{stripePaymentMethod: PaymentMethod}  $gatewayData
     *
     * @throws PaymentGatewayException
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        switch ($this->getCheckoutType()) {
            case 'modal':
                return give(PaymentGatewayRegister::class)
                    ->getPaymentGateway(CreditCardGateway::id())
                    ->createPayment($donation, $gatewayData);
            case 'redirect':
                return $this->createPaymentRedirect($donation);
            default:
                throw new CheckoutException('Invalid Checkout Error');
        }
    }

    /**
     * @since 2.21.2
     */
    public function getCheckoutType(): string
    {
        return give_stripe_get_checkout_type();
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
     * @since 2.23.1
     *
     * @return string|void
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        Stripe::canShowBillingAddress($formId, $args);

        switch ($checkoutType = give_stripe_get_checkout_type()) {
            case 'redirect':
                return $this->getCheckoutInstructions();
            case 'modal':
                return $this->getCheckoutInstructions()
                       . $this->getCheckoutModalHTML($formId, $args);
            default:
                throw new CheckoutTypeException($checkoutType);
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
