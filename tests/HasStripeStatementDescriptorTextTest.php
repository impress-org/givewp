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
        $this->filterStatementDescriptor('demo');
    }

    public function testTextGreaterThenTwentyTwoLetter()
    {
        $this->expectExceptionMessage('Stripe statement descriptor text contain between 5 - 22 letters, inclusive.');
        $this->filterStatementDescriptor('This is a long stripe statement descriptor.');
    }

    public function testTextWithReserveWords()
    {
        $this->expectExceptionMessage(
            'Stripe statement descriptor text should not contain any of the special characters <code>< > \ \' " *</code>.'
        );
        $this->filterStatementDescriptor('* < > " \' \\');
    }

    public function testTextWithText()
    {
        $this->assertSame( get_bloginfo('name'), $this->filterStatementDescriptor(get_bloginfo('name')));
    }
}
