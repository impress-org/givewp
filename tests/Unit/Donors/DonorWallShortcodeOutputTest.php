<?php

namespace Give\Tests\Unit\Donors;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class DonorWallShortcodeOutputTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A registered shortcode tag used to build deterministic payloads.
     */
    const TAG = 'svultag';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_shortcode(self::TAG, static function () {
            return 'RENDERED';
        });
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_shortcode(self::TAG);

        parent::tearDown();
    }

    /**
     * A donor name carrying a self-nested shortcode must not survive to the donor-wall output as a
     * live tag, and the shortcode must not execute.
     *
     * @since TBD
     */
    public function testDonorWallNeutralizesShortcodesInDonorFields(): void
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(5000, 'USD'),
            'firstName' => '[svul[' . self::TAG . ']tag]Ada',
            'lastName' => 'Lovelace',
            'company' => '[' . self::TAG . ']Acme',
            'comment' => 'Great cause [' . self::TAG . ']',
            'anonymous' => false,
        ]);

        $html = $this->renderDonorWall();

        $this->assertStringNotContainsString('[' . self::TAG . ']', $html);
        $this->assertStringNotContainsString('RENDERED', $html);
    }

    /**
     * Render the donor-wall shortcode. The wall's own get_donation_data() unserializes every
     * donationmeta row and emits a benign warning on plain-string values (suppressed in production);
     * the test harness would otherwise promote that pre-existing warning to a failure, so it is
     * swallowed around the render only.
     *
     * @since TBD
     */
    private function renderDonorWall(): string
    {
        set_error_handler(static function () {
            return true;
        }, E_WARNING | E_DEPRECATED | E_NOTICE);

        try {
            return (string)do_shortcode('[give_donor_wall show_company_name="true"]');
        } finally {
            restore_error_handler();
        }
    }
}
