<?php

namespace Give\PaymentGateways\Stripe\Traits;

/**
 * @unreleased
 */
trait HasStripeStatementDescriptorText
{
    /**
     * Return filtered stripe statement descriptor text.
     * Check Stripe statement descriptor requirements: https://stripe.com/docs/statement-descriptors#requirements
     *
     * @unreleased
     * @return string
     */
    private function filterStatementDescriptor( $statementDescriptor )
    {
        $maxLength = 22;
        $minLength = 5;
        $unsupportedCharacters = ['<', '>', '"', '\\', '\'', '*']; // Reserve keywords.
        $statementDescriptor = mb_substr($statementDescriptor, 0, $maxLength);
        $statementDescriptor = str_replace($unsupportedCharacters, '', $statementDescriptor);
        $statementDescriptor = give_clean($statementDescriptor);

        return $minLength > strlen($statementDescriptor) ? '' : $statementDescriptor;
    }
}
