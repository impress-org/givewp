<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\Traits\HandlePaymentIntentStatus;
use Give\PaymentGateways\Gateways\Stripe\Traits\SEPAMandateForm;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

/**
 * @since 2.19.0
 */
class SEPAGateway extends PaymentGateway
{
    use SEPAMandateForm;
    use HandlePaymentIntentStatus;

    /** @var array */
    protected $errorMessages = [];

    public function __construct(SubscriptionModuleInterface $subscriptionModule = null)
    {
        parent::__construct($subscriptionModule);

        $this->errorMessages['accountConfiguredNoSsl']    = esc_html__( 'IBAN fields are disabled because your site is not running securely over HTTPS.', 'give' );
        $this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__( 'IBAN fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give' );
        $this->errorMessages['accountNotConfigured']      = esc_html__( 'IBAN fields are disabled. Please connect and configure your Stripe account to accept donations.', 'give' );
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

        $workflow->action( new Actions\SaveDonationSummary );
        $workflow->action( new Actions\GetPaymentMethodFromRequest );
        $workflow->action( new Actions\GetOrCreateStripeCustomer );
        $workflow->action( new Actions\CreatePaymentIntent(
            $this->getPaymentIntentArgs()
        ));

        return $this->handlePaymentIntentStatus(
            $workflow->resolve( PaymentIntent::class )
        );
    }

    /**
     * @inheritDoc
     */
    public static function id()
    {
        return 'stripe_sepa';
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
        return __('Stripe - SEPA Direct Debit', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return __('Stripe - SEPA Direct Debit', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        return $this->getMandateFormHTML( $formId, $args );
    }

    /**
     * @since 2.19.0
     * @return array
     */
    protected function getPaymentIntentArgs()
    {
        return [
            'payment_method_types' => [ 'sepa_debit' ],
            'setup_future_usage'   => 'on_session',
            'mandate_data'         => [
                'customer_acceptance' => [
                    'type'   => 'online',
                    'online' => [
                        'ip_address' => give_stripe_get_ip_address(),
                        'user_agent' => give_get_user_agent(),
                    ],
                ],
            ],
        ];
    }
}
