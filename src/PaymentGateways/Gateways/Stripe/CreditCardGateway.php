<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

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

        $this->errorMessages['accountConfiguredNoSsl']    = esc_html__( 'Credit Card fields are disabled because your site is not running securely over HTTPS.', 'give' );
        $this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__( 'Credit Card fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give' );
        $this->errorMessages['accountNotConfigured']      = esc_html__( 'Credit Card fields are disabled. Please connect and configure your Stripe account to accept donations.', 'give' );
    }

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

        $workflow->action( new Actions\GetPaymentMethodFromRequest );
        $workflow->action( new Actions\SaveDonationSummary );
        $workflow->action( new Actions\GetOrCreateStripeCustomer );
        $workflow->action( new Actions\CreatePaymentIntent );

        return $this->handlePaymentIntentStatus(
            $workflow->resolve( PaymentIntent::class )
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
        return $this->getCreditCardFormHTML( $formId, $args );
    }
}
