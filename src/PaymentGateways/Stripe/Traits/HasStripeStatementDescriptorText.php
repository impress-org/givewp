<?php

namespace Give\PaymentGateways\Stripe\Traits;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

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
        $unsupportedCharacters = ['<', '>', '"', '\\', '\'', '*']; // Stripe reserve keywords.

        if ($minLength > strlen($statementDescriptor) || $maxLength < strlen($statementDescriptor)) {
            throw new InvalidArgumentException(
                esc_html__('Stripe statement descriptor text should contain between 5 - 22 letters, inclusive.', 'give')
            );
        }

        if (array_intersect($unsupportedCharacters, str_split($statementDescriptor))) {
            throw new InvalidArgumentException(
                __(
                    'Stripe statement descriptor text should not contain any of the special characters <code>< > \ \' " *</code>.',
                    'give'
                )
            );
        }

        return $statementDescriptor;
    }
}
