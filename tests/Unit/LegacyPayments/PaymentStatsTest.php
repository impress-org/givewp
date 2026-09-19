<?php

namespace Give\Tests\Unit\LegacyPayments;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache;
use Give_Payment_Stats;

/**
 * @since TBD
 */
class PaymentStatsTest extends TestCase
{
    use RefreshDatabase;

    /** @var Campaign */
    private $campaign;

    public function setUp(): void
    {
        parent::setUp();
        Donation::query()->delete();
        Give_Cache::flush_cache(true);
        $this->campaign = Campaign::factory()->create();
    }

    /**
     * @since TBD
     */
    public function testEarningsAndSalesMatchTheLoopBasedCalculation()
    {
        $this->donation(1000, '-10 days');
        $this->donation(2550, '-5 days');
        $this->donation(400, '-5 days', DonationStatus::PENDING());
        $this->donation(9999, '-400 days');

        $stats = new Give_Payment_Stats();
        $start = strtotime('-30 days');
        $end = time();

        $this->assertEquals(35.5, $stats->get_earnings(0, $start, $end));
        $this->assertSame(2, $stats->get_sales(0, $start, $end));

        Give_Cache::flush_cache(true);
        add_filter('givewp_payment_stats_aggregate_in_sql', '__return_false');

        $this->assertEquals(35.5, $stats->get_earnings(0, $start, $end));
        $this->assertSame(2, $stats->get_sales(0, $start, $end));

        remove_filter('givewp_payment_stats_aggregate_in_sql', '__return_false');
    }

    /**
     * @since TBD
     */
    public function testFiltersByFormAndGateway()
    {
        $otherForm = $this->donation(500, '-2 days');
        give()->payment_meta->update_meta($otherForm->id, '_give_payment_form_id', 999999);
        $this->donation(1200, '-2 days');
        $stripe = $this->donation(300, '-2 days');
        give()->payment_meta->update_meta($stripe->id, '_give_payment_gateway', 'stripe');

        $stats = new Give_Payment_Stats();
        $formId = $this->campaign->defaultForm()->id;

        $this->assertEquals(15.0, $stats->get_earnings($formId, strtotime('-7 days'), time()));
        $this->assertSame(2, $stats->get_sales($formId, strtotime('-7 days'), time()));
        $this->assertEquals(3.0, $stats->get_earnings(0, strtotime('-7 days'), time(), 'stripe'));
    }

    /**
     * Currency Switcher stores a base-currency amount; stats use it when present.
     *
     * @since TBD
     */
    public function testUsesCurrencySwitcherBaseAmountWhenPresent()
    {
        $converted = $this->donation(10000, '-1 day');
        give()->payment_meta->update_meta($converted->id, '_give_cs_base_amount', '87.25');
        $this->donation(1000, '-1 day');

        $this->assertEquals(97.25, (new Give_Payment_Stats())->get_earnings(0, strtotime('-7 days'), time()));
    }

    /**
     * Anything an add-on adds to the query args that this code cannot translate falls back to the
     * per-donation path, so results stay correct even if slower.
     *
     * @since TBD
     */
    public function testFallsBackWhenArgsContainUnknownKeys()
    {
        $this->donation(1000, '-1 day');
        $filter = static function ($args) {
            $args['give_stats_unknown_key'] = 1;
            return $args;
        };
        add_filter('give_stats_earnings_args', $filter);

        $this->assertEquals(10.0, (new Give_Payment_Stats())->get_earnings(0, strtotime('-7 days'), time()));

        remove_filter('give_stats_earnings_args', $filter);
    }

    /**
     * @since TBD
     */
    public function testRunsDonationAmountFiltersWhenAnAddOnRegistersOne()
    {
        $this->donation(1000, '-1 day');
        $double = static function ($amount) {
            return give_maybe_sanitize_amount($amount) * 2;
        };
        add_filter('give_donation_amount', $double);

        $this->assertEquals(20.0, (new Give_Payment_Stats())->get_earnings(0, strtotime('-7 days'), time()));

        remove_filter('give_donation_amount', $double);
    }

    private function donation(int $cents, string $when, ?DonationStatus $status = null): Donation
    {
        $created = new DateTime($when);

        return Donation::factory()->create([
            'campaignId' => $this->campaign->id,
            'formId' => $this->campaign->defaultForm()->id,
            'status' => $status ?: DonationStatus::COMPLETE(),
            'gatewayId' => 'manual',
            'amount' => new Money($cents, 'USD'),
            'createdAt' => $created,
            'updatedAt' => $created,
        ]);
    }
}
