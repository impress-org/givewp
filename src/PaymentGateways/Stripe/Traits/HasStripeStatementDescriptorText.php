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
     *
     * @param string $statementDescriptor
     *
     * @return string
     */
    protected function filterStatementDescriptor($statementDescriptor)
    {
        $maxLength = 22;
        $minLength = 5;
        $unsupportedCharacters = ['<', '>', '"', '\\', '\'', '*']; // Reserve keywords.
        $statementDescriptor = substr($statementDescriptor, 0, $maxLength);
        $statementDescriptor = str_replace($unsupportedCharacters, '', $statementDescriptor);

        return $minLength > strlen($statementDescriptor) ? '' : trim($statementDescriptor);
    }
}
