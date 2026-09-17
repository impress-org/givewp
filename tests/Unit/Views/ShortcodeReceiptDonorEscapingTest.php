<?php

namespace Give\Tests\Unit\Views;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Covers the donor name and company output of templates/shortcode-receipt.php.
 *
 * @since 4.16.9
 */
final class ShortcodeReceiptDonorEscapingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A registered shortcode tag used to build deterministic payloads.
     */
    const TAG = 'svultag';

    /**
     * @since 4.16.9
     */
    public function setUp(): void
    {
        parent::setUp();

        add_shortcode(self::TAG, static function () {
            return 'RENDERED';
        });
    }

    /**
     * @since 4.16.9
     */
    public function tearDown(): void
    {
        remove_shortcode(self::TAG);

        parent::tearDown();
    }

    /**
     * @since 4.16.9
     */
    public function testNeutralizesShortcodesInDonorNameAndCompany(): void
    {
        $firstNameInput = '[svul[' . self::TAG . ']tag]Ada';
        $lastName = 'Lovelace';
        $companyInput = '[' . self::TAG . ']Acme';

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(5000, 'USD'),
            'firstName' => $firstNameInput,
            'lastName' => $lastName,
            'company' => $companyInput,
        ]);

        $html = $this->renderReceipt($donation->id);

        $expectedName = esc_html(give_strip_shortcodes_deep(trim("$firstNameInput $lastName")));
        $expectedCompany = esc_html(give_strip_shortcodes_deep($companyInput));

        $this->assertStringContainsString($expectedName, $html);
        $this->assertStringContainsString($expectedCompany, $html);

        $this->assertStringNotContainsString('[' . self::TAG . ']', $html);
        $this->assertStringNotContainsString('RENDERED', $html);
    }

    /**
     * Render templates/shortcode-receipt.php directly with the globals it reads.
     *
     * @since 4.16.9
     */
    private function renderReceipt(int $donationId): string
    {
        global $donation, $give_receipt_args;

        $donation = get_post($donationId);
        $give_receipt_args = [
            'id' => $donationId,
            'donor' => true,
            'company_name' => true,
            'date' => false,
            'price' => false,
            'payment_status' => false,
            'payment_id' => false,
            'payment_method' => false,
            'status_notice' => false,
        ];

        ob_start();
        require GIVE_PLUGIN_DIR . 'templates/shortcode-receipt.php';

        return (string)ob_get_clean();
    }
}
