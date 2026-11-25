<?php

namespace Give\Tests\Unit\FormBuilder\EmailPreview\Actions;

use Give\FormBuilder\EmailPreview\Actions\ApplyPreviewTemplateTags;
use Give\Tests\TestCase;

/**
 * @since 4.20.0
 */
class ApplyPreviewTemplateTagsTest extends TestCase
{
    /**
     * @since 4.20.0
     */
    public function testPaymentIdTagBecomesASampleNumberInRange()
    {
        $message = (new ApplyPreviewTemplateTags())('ID: {payment_id}');

        $this->assertMatchesRegularExpression('/^ID: \d+$/', $message);

        $paymentId = (int)substr($message, 4);

        $this->assertGreaterThanOrEqual(2000, $paymentId);
        $this->assertLessThanOrEqual(2050, $paymentId);
    }

    /**
     * @since 4.20.0
     */
    public function testDonationTagBecomesTheSampleFormTitle()
    {
        $this->assertSame(
            'Sample Donation Form Title',
            (new ApplyPreviewTemplateTags())('{donation}')
        );
    }
}
