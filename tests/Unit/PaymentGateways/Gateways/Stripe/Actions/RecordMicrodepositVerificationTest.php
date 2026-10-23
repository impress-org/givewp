<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\PaymentGateways\Gateways\Stripe\Actions\RecordMicrodepositVerification;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class RecordMicrodepositVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array[] Arguments of each intercepted `wp_mail()` call.
     */
    private $sentEmails = [];

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_all_filters('pre_wp_mail');

        parent::tearDown();
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function itRetriesTheEmailWhenThePreviousSendFailed(): void
    {
        $hostedVerificationUrl = 'https://payments.stripe.com/microdeposit/' . wp_generate_password(12, false);
        $donation = Donation::factory()->create();

        add_filter('pre_wp_mail', '__return_false');

        $firstCallEmailed = (new RecordMicrodepositVerification())($donation, $hostedVerificationUrl);

        self::assertFalse($firstCallEmailed);
        self::assertSame(
            $hostedVerificationUrl,
            give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true)
        );
        self::assertEmpty(give_get_meta($donation->id, RecordMicrodepositVerification::EMAILED_META_KEY, true));

        remove_all_filters('pre_wp_mail');
        $this->captureEmails();

        $secondCallEmailed = (new RecordMicrodepositVerification())($donation, $hostedVerificationUrl);

        self::assertTrue($secondCallEmailed);
        self::assertCount(1, $this->sentEmails);
        self::assertSame(
            $hostedVerificationUrl,
            give_get_meta($donation->id, RecordMicrodepositVerification::EMAILED_META_KEY, true)
        );
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function itDoesNotEmailTheSameLinkTwice(): void
    {
        $hostedVerificationUrl = 'https://payments.stripe.com/microdeposit/' . wp_generate_password(12, false);
        $donation = Donation::factory()->create();
        $this->captureEmails();

        $action = new RecordMicrodepositVerification();
        $action($donation, $hostedVerificationUrl);
        $secondCallEmailed = $action($donation, $hostedVerificationUrl);

        self::assertFalse($secondCallEmailed);
        self::assertCount(1, $this->sentEmails);
        self::assertCount(2, $donation->notes()->getAll());
    }

    /**
     * @since TBD
     */
    private function captureEmails(): void
    {
        add_filter('pre_wp_mail', function ($return, $atts) {
            $this->sentEmails[] = $atts;

            return true;
        }, 10, 2);
    }
}
