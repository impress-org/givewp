<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Traits;

use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;
use Give\Tests\TestCase;

/**
 * @since 2.19.0
 */
class HasStripeStatementDescriptorTextTest extends TestCase
{
    use HasStripeStatementDescriptorText;

    public function testTextLessThenFiveLetter()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should contain between 5 - 22 letters, inclusive.'
        );
        $this->validateStatementDescriptor('demo');
    }

    public function testTextGreaterThenTwentyTwoLetter()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should contain between 5 - 22 letters, inclusive.'
        );
        $this->validateStatementDescriptor('This is a long stripe statement descriptor.');
    }

    public function testTextWithNumericLetters()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should contain at least one letter.'
        );
        $this->validateStatementDescriptor('123456');
    }

    public function testTextWithReserveWords()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should not contain any of the special characters <code>< > \ \' " *</code>.'
        );
        $this->validateStatementDescriptor('* < > " \' \\');
    }
}
