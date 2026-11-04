<?php

namespace Give\Tests\Unit\FormBuilder\EmailPreview\Actions;

use Give\FormBuilder\EmailPreview\Actions\ApplyPreviewTemplateTags;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class ApplyPreviewTemplateTagsTest extends TestCase
{
    /**
     * @since TBD
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
     * @since TBD
     */
    public function testDonationTagBecomesTheSampleFormTitle()
    {
        $this->assertSame(
            'Sample Donation Form Title',
            (new ApplyPreviewTemplateTags())('{donation}')
        );
    }
}
