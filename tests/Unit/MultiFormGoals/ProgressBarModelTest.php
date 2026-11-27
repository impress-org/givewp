<?php

namespace Give\Tests\Unit\MultiFormGoals;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\MultiFormGoals\ProgressBar\Model;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Covers the progress bar model when the configured forms do not match any form.
 *
 * @since TBD
 */
class ProgressBarModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        Donation::query()->delete();
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
    public function testTotalAndCountAreZeroWhenNoFormsMatch()
    {
        $form = Campaign::factory()->create()->defaultForm();

        Donation::factory()->create([
            'formId' => $form->id,
            'mode' => DonationMode::LIVE(),
            'status' => DonationStatus::COMPLETE(),
            'gatewayId' => 'manual',
            'amount' => new Money(1000, 'USD'),
        ]);

        $model = new Model(['ids' => [999999]]);

        $this->assertSame('0', $model->getTotal());
        $this->assertSame(0, $model->getDonationCount());
    }
}
