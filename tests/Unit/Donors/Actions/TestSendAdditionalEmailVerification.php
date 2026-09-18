<?php

namespace Give\Tests\Unit\Donors\Actions;

use Give\Donors\Actions\SendAdditionalEmailVerification;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @coversDefaultClass SendAdditionalEmailVerification
 */
class TestSendAdditionalEmailVerification extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, array{to: string, subject: string, message: string}>
     */
    private $sentMail = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sentMail = [];

        add_filter('pre_wp_mail', function ($null, $attributes) {
            $this->sentMail[] = [
                'to' => $attributes['to'],
                'subject' => $attributes['subject'],
                'message' => $attributes['message'],
            ];

            return false;
        }, 10, 2);
    }

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
     */
    public function testSendShouldMailVerificationLinkToTheNewEmail(): void
    {
        $donor = Donor::factory()->create();

        (new SendAdditionalEmailVerification())($donor->id, 'verifyme@givewp.com');

        $pending = (new DonorPendingEmailRepository())->all($donor->id);

        $this->assertCount(1, $pending);
        $this->assertSame('verifyme@givewp.com', $pending[0]['email']);
        $this->assertCount(1, $this->sentMail);
        $this->assertSame('verifyme@givewp.com', $this->sentMail[0]['to']);
        $this->assertStringContainsString($pending[0]['token'], $this->sentMail[0]['message']);
        $this->assertStringContainsString('givewp_verify_email', $this->sentMail[0]['message']);
    }

    /**
     * @since TBD
     */
    public function testSendShouldNotMailWhenTheEmailIsRejected(): void
    {
        $donor = Donor::factory()->create();
        Donor::factory()->create(['email' => 'claimed@givewp.com']);

        (new SendAdditionalEmailVerification())($donor->id, 'claimed@givewp.com');

        $this->assertSame([], $this->sentMail);
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
    }
}
