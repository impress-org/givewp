<?php

namespace Give\PaymentGateways\Stripe\Traits;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * @since 2.19.0
 */
trait HasStripeStatementDescriptorText
{
    /**
     * Return filtered stripe statement descriptor text.
     * Check Stripe statement descriptor requirements: https://stripe.com/docs/statement-descriptors#requirements
     *
     * @since 2.19.0
     *
     * @param string $statementDescriptor
     */
    protected function validateStatementDescriptor($statementDescriptor)
    {
        $maxLength = 22;
        $minLength = 5;
        $unsupportedCharacters = ['<', '>', '"', '\\', '\'', '*']; // Stripe reserve keywords.

        if ($minLength > strlen($statementDescriptor) || $maxLength < strlen($statementDescriptor)) {
            throw new InvalidArgumentException(
                esc_html__('Stripe statement descriptor text should contain between 5 - 22 letters, inclusive.', 'give')
            );
        }

        if (is_numeric($statementDescriptor)) {
            throw new InvalidArgumentException(
                esc_html__('Stripe statement descriptor text should contain at least one letter.', 'give')
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
    }

    /**
     * Return filtered statement descriptor.
     * This function should be used to filter statement description
     * which was storing in stripe_statement_descriptor give setting prior to Giver 2.19.
     *
     * @since 2.19.1
     * @deprecated
     *
     * @param string $text
     *
     * @return false|string
     */
    protected function filterOldStatementDescriptor($text)
    {
        $statementDescriptor = trim($text);
        $unsupportedCharacters = ['<', '>', '"', '\''];
        $statementDescriptor = str_replace($unsupportedCharacters, '', $statementDescriptor);
        return substr($statementDescriptor, 0, 22);
    }
}
