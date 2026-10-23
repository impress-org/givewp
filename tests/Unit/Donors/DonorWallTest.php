<?php

namespace Give\Tests\Unit\Donors;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Covers the queries of the donor wall shortcode.
 *
 * @since TBD
 */
class DonorWallTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The donated amount of each donor is used as the marker of that donor in the rendered wall.
     * The currency symbol is left out, because it is an HTML entity that can be written in
     * different ways (`&#36;` or `&#036;`) depending on how the output is escaped.
     */
    private const ADA = '10.00';

    private const GRACE = '50.00';

    /** @var DonationForm */
    private $formOne;

    /** @var DonationForm */
    private $formTwo;

    /** @var Donor */
    private $ada;

    /** @var Donor */
    private $grace;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->formOne = DonationForm::factory()->create();
        $this->formTwo = DonationForm::factory()->create();
        $this->ada = Donor::factory()->create();
        $this->grace = Donor::factory()->create();

        $this->donate($this->ada, 'Adaxx', 'Lovelacexx', $this->formOne, 1000, false, '2026-01-01 10:00:00');
        $this->donate($this->grace, 'Gracexx', 'Hopperxx', $this->formTwo, 5000, true, '2026-01-02 10:00:00');
    }

    /**
     * @since TBD
     */
    private function donate(Donor $donor, string $firstName, string $lastName, DonationForm $form, int $cents, bool $withComment, string $date): Donation
    {
        return Donation::factory()->create([
            'formId' => $form->id,
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money($cents, 'USD'),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'comment' => $withComment ? 'A kind comment' : null,
            'anonymous' => false,
            'createdAt' => new \DateTime($date),
        ]);
    }

    /**
     * @since TBD
     */
    private function render(string $atts = ''): string
    {
        return (string)do_shortcode('[give_donor_wall ' . $atts . ']');
    }

    /**
     * @since TBD
     */
    public function testShowsAllDonorsByDefault()
    {
        $html = $this->render();

        $this->assertStringContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);
    }

    /**
     * @since TBD
     */
    public function testFilterByFormId()
    {
        $html = $this->render('form_id="' . $this->formOne->id . '"');

        $this->assertStringContainsString(self::ADA, $html);
        $this->assertStringNotContainsString(self::GRACE, $html);

        $html = $this->render('form_id="' . $this->formTwo->id . '"');

        $this->assertStringNotContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);
    }

    /**
     * @since TBD
     */
    public function testFilterByMoreThanOneFormId()
    {
        $html = $this->render('form_id="' . $this->formOne->id . ',' . $this->formTwo->id . '"');

        $this->assertStringContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);
    }

    /**
     * @since TBD
     */
    public function testFilterByDonorIds()
    {
        $html = $this->render('ids="' . $this->grace->id . '"');

        $this->assertStringNotContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);

        $html = $this->render('ids="' . $this->ada->id . ',' . $this->grace->id . '"');

        $this->assertStringContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);
    }

    /**
     * @since TBD
     */
    public function testOnlyComments()
    {
        $html = $this->render('only_comments="true"');

        $this->assertStringNotContainsString(self::ADA, $html);
        $this->assertStringContainsString(self::GRACE, $html);
        $this->assertStringContainsString('A kind comment', $html);
    }

    /**
     * @since TBD
     */
    public function testOrderByDate()
    {
        $desc = $this->render('order="DESC"');
        $asc = $this->render('order="ASC"');

        $this->assertLessThan(strpos($desc, self::ADA), strpos($desc, self::GRACE));
        $this->assertLessThan(strpos($asc, self::GRACE), strpos($asc, self::ADA));
    }

    /**
     * @since TBD
     */
    public function testOrderByDonationAmount()
    {
        $desc = $this->render('orderby="donation_amount" order="DESC"');
        $asc = $this->render('orderby="donation_amount" order="ASC"');

        $this->assertLessThan(strpos($desc, self::ADA), strpos($desc, self::GRACE));
        $this->assertLessThan(strpos($asc, self::GRACE), strpos($asc, self::ADA));
    }

    /**
     * @since TBD
     */
    public function testDonorsPerPageLimitsTheResult()
    {
        $html = $this->render('donors_per_page="1" order="DESC"');

        $this->assertStringContainsString(self::GRACE, $html);
        $this->assertStringNotContainsString(self::ADA, $html);
    }

    /**
     * An order value outside the allowlist falls back to DESC instead of reaching the SQL.
     *
     * @since TBD
     */
    public function testInvalidOrderFallsBackToDefault()
    {
        $html = $this->render('order="ASC; DROP TABLE x"');

        $this->assertLessThan(strpos($html, self::ADA), strpos($html, self::GRACE));
    }
}
