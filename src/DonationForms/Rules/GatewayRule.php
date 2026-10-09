<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class GatewayRule implements ValidationRule, ValidatesOnFrontEnd
{

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'gatewayId';
    }

    /**
     * @since 4.17.0 Declare the nullable parameter explicitly.
     * @since 3.0.0
     */
    public static function fromString(?string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @since TBD Number the placeholders and add translators comments.
     * @since 3.0.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $supportedGateways = give(DonationFormRepository::class)->getEnabledPaymentGateways($values['formId']);

        // get all the supported gateway ids
        $gatewayIds = array_keys($supportedGateways);

        if (!in_array($value, $gatewayIds, true)) {
            $fail(
                sprintf(
                    /* translators: 1: Field label, 2: Comma-separated list of valid gateway IDs */
                    __('%1$s must be a valid gateway.  Valid gateways are: %2$s', 'give'),
                    '{field}',
                    implode(
                        ', ',
                        $gatewayIds
                    )
                )
            );
        }
    }

    /**
     * @since 3.0.0
     */
    public function serializeOption()
    {
        return null;
    }
}
