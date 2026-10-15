<?php

namespace Give\Tests\Unit\MultiFormGoals;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\MultiFormGoals\ProgressBar\Query;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Covers the revenue query of the multi-form goal progress bar. The total is in the smallest
 * currency unit, because that is how the revenue table stores amounts.
 *
 * @since TBD
 */
class ProgressBarQueryTest extends TestCase
{
    use RefreshDatabase;

    /** @var int */
    private $formA;

    /** @var int */
    private $formB;

    /** @var int */
    private $formC;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        Donation::query()->delete();
        $this->formA = Campaign::factory()->create()->defaultForm()->id;
        $this->formB = Campaign::factory()->create()->defaultForm()->id;
        $this->formC = Campaign::factory()->create()->defaultForm()->id;

        add_filter('give_is_test_mode', '__return_false');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_filter('give_is_test_mode', '__return_false');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testTotalAndCountMatchTheLiveDonationsOfTheGivenForms()
    {
        $this->donation($this->formA, 1000, DonationMode::LIVE());
        $this->donation($this->formA, 2500, DonationMode::LIVE());
        $this->donation($this->formB, 400, DonationMode::LIVE());
        $this->donation($this->formC, 9999, DonationMode::LIVE());

        $results = (new Query([$this->formA, $this->formB]))->getResults();

        $this->assertSame(3, (int)$results->count);
        $this->assertEquals(3900.0, (float)$results->total);
    }

    /**
     * @since TBD
     */
    public function testSingleFormOnlyCountsThatForm()
    {
        $this->donation($this->formA, 1000, DonationMode::LIVE());
        $this->donation($this->formB, 400, DonationMode::LIVE());

        $results = (new Query([$this->formB]))->getResults();

        $this->assertSame(1, (int)$results->count);
        $this->assertEquals(400.0, (float)$results->total);
    }

    /**
     * @since TBD
     */
    public function testWithoutFormsEveryFormIsCounted()
    {
        $this->donation($this->formA, 1000, DonationMode::LIVE());
        $this->donation($this->formC, 400, DonationMode::LIVE());

        $results = (new Query([]))->getResults();

        $this->assertSame(2, (int)$results->count);
        $this->assertEquals(1400.0, (float)$results->total);
    }

    /**
     * @since TBD
     */
    public function testTestModeCountsOnlyTestDonations()
    {
        $this->donation($this->formA, 1000, DonationMode::LIVE());
        $this->donation($this->formA, 300, DonationMode::TEST());
        $this->donation($this->formB, 200, DonationMode::TEST());

        remove_filter('give_is_test_mode', '__return_false');
        add_filter('give_is_test_mode', '__return_true');
        $results = (new Query([$this->formA, $this->formB]))->getResults();
        remove_filter('give_is_test_mode', '__return_true');

        $this->assertSame(2, (int)$results->count);
        $this->assertEquals(500.0, (float)$results->total);
    }

    /**
     * @since TBD
     */
    private function donation(int $formId, int $cents, DonationMode $mode): Donation
    {
        return Donation::factory()->create([
            'formId' => $formId,
            'mode' => $mode,
            'status' => DonationStatus::COMPLETE(),
            'gatewayId' => 'manual',
            'amount' => new Money($cents, 'USD'),
        ]);
    }
}
