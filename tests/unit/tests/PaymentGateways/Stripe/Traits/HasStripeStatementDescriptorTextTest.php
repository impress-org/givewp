<?php

use Give\PaymentGateways\Stripe\Traits\HasStripeStatementDescriptorText;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class HasStripeStatementDescriptorTextTest extends TestCase
{
    use HasStripeStatementDescriptorText;

    public function testTextLessThenFiveLetter()
    {
        $this->expectExceptionMessage('Stripe statement descriptor text contain between 5 - 22 letters, inclusive.');
        $this->validateStatementDescriptor('demo');
    }

    public function testTextGreaterThenTwentyTwoLetter()
    {
        $this->expectExceptionMessage('Stripe statement descriptor text contain between 5 - 22 letters, inclusive.');
        $this->validateStatementDescriptor('This is a long stripe statement descriptor.');
    }

    public function testTextWithReserveWords()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should not contain any of the special characters <code>< > \ \' " *</code>.'
        );
        $this->validateStatementDescriptor('* < > " \' \\');
    }

    public function testTextWithText()
    {
        $this->assertSame( get_bloginfo('name'), $this->validateStatementDescriptor(get_bloginfo('name')));
    }
}
