<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

/**
 * @since 2.19.0
 */
class CreditCardGateway extends PaymentGateway
{
    use Traits\CreditCardForm;
    use Traits\HandlePaymentIntentStatus;

    /** @var array */
    protected $errorMessages = [];

    public function __construct(SubscriptionModule $subscriptionModule = null)
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
     *
     * @param array{stripePaymentMethod: PaymentMethod} $gatewayData
     *
     * @throws PaymentGatewayException
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        $donationSummary = Call::invoke(Actions\SaveDonationSummary::class, $donation);
        $stripeCustomer = Call::invoke(Actions\GetOrCreateStripeCustomer::class, $donation);

        $createIntentAction = new Actions\CreatePaymentIntent([]);

        return $this->handlePaymentIntentStatus(
            $createIntentAction(
                $donation,
                $donationSummary,
                $stripeCustomer,
                $gatewayData['stripePaymentMethod']
            ),
            $donation
        );
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe';
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
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('Stripe - Credit Card', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return $this->getCreditCardFormHTML($formId, $args);
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
