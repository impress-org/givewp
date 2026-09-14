<?php

namespace Give\Tests\Unit\Views;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Covers the donor-name cell of templates/history-donations.php, which prints
 * esc_html( give_strip_shortcodes_deep( give_get_donor_name_by( $post->ID ) ) ).
 *
 * The full template resolves the current user's donations through legacy session/email-access
 * plumbing, so this asserts the exact sink expression the template uses rather than driving that
 * plumbing.
 *
 * @since TBD
 */
final class DonationHistoryDonorEscapingTest extends TestCase
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
     * @since TBD
     */
    public function testDonorNameSinkExpressionNeutralizesShortcodes(): void
    {
        $firstNameInput = '[svul[' . self::TAG . ']tag]Ada';

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'firstName' => $firstNameInput,
            'lastName' => 'Lovelace',
        ]);

        // The raw source is tainted with a self-nested shortcode that survives a single strip pass.
        $rawName = give_get_donor_name_by($donation->id);
        $this->assertStringContainsString('[' . self::TAG . ']', strip_shortcodes($rawName));

        // The template's sink expression neutralizes it.
        $output = esc_html(give_strip_shortcodes_deep($rawName));

        $this->assertStringNotContainsString('[' . self::TAG . ']', $output);
        $this->assertStringContainsString('Ada Lovelace', $output);
    }
}
