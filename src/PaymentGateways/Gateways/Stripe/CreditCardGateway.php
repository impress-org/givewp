<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Form\Utils as FormUtils;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

/**
 * @unreleased
 */
class CreditCardGateway extends PaymentGateway
{
    use Traits\CreditCardForm;
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
        if (FormUtils::isLegacyForm($formId)) {
            return false;
        }

        return $this->getCreditCardFormHTML( $formId, $args );
    }
}
