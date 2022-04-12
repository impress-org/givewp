<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @since 2.19.0
 */
class CreditCardGateway extends PaymentGateway
{
    use Traits\CreditCardForm;
    use Traits\HandlePaymentIntentStatus;

    /** @var array */
    protected $errorMessages = [];

    public function __construct(SubscriptionModuleInterface $subscriptionModule = null)
    {
        parent::__construct($subscriptionModule);

        $this->errorMessages['accountConfiguredNoSsl'] = esc_html__(
            'Credit Card fields are disabled because your site is not running securely over HTTPS.',
            'give'
        );
        $this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__(
            'Credit Card fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.',
            'give'
        );
        $this->errorMessages['accountNotConfigured'] = esc_html__(
            'Credit Card fields are disabled. Please connect and configure your Stripe account to accept donations.',
            'give'
        );
    }

    /**
     * @inheritDoc
     * @since 2.19.7 fix handlePaymentIntentStatus not receiving extra param
     * @since 2.19.0
     * @return GatewayCommand
     * @throws PaymentGatewayException
     */
    public function createPayment(GatewayPaymentData $paymentData)
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
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        return $this->getCreditCardFormHTML($formId, $args);
    }
}
