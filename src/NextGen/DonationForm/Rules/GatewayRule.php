<?php
namespace Give\NextGen\DonationForm\Rules;

use Closure;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class GatewayRule implements ValidationRule, ValidatesOnFrontEnd
{

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'gatewayId';
    }

    /**
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @unreleased
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        /** @var PaymentGatewayRegister $paymentGatewayRegistrar */
        $paymentGatewayRegistrar = give(PaymentGatewayRegister::class);

        // get all registered gateways
        $registeredPaymentGateways = $paymentGatewayRegistrar->getPaymentGateways();

        // get all next gen supported gateways
        $supportedGateways = array_filter(
            $registeredPaymentGateways,
            static function ($gateway) {
                return is_a($gateway, NextGenPaymentGatewayInterface::class, true);
            }
        );

        // get all the supported gateway ids
        $gatewayIds = array_keys($supportedGateways);

        if (!in_array($value, $gatewayIds, true)) {
            $fail(
                sprintf(
                    __('%s must be a valid gateway.  Valid gateways are: %s', 'give'),
                    '{field}',
                    implode(
                        ', ',
                        $supportedGateways
                    )
                )
            );
        }
    }

    /**
     * @unreleased
     */
    public function serializeOption()
    {
        return null;
    }
}