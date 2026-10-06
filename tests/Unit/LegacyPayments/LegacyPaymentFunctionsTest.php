<?php

namespace Give\Tests\Unit\LegacyPayments;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache;
use Give_Payment_Stats;

/**
 * Covers the legacy payment functions and queries that build SQL by hand.
 *
 * The tests for a payment status with a quote and for a status given as a plain string were added
 * after the SQL fix, because they check the new hardening of Give_Payments_Query::get_sql(). Every
 * other test was written first and passed on the unchanged code.
 *
 * @since TBD
 */
class LegacyPaymentFunctionsTest extends TestCase
{
    use RefreshDatabase;

    /** @var Campaign */
    private $campaign;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        Donation::query()->delete();
        Give_Cache::delete_all_expired(true);
        $this->campaign = Campaign::factory()->create();
    }

    /**
     * @since TBD
     */
    public function testTotalEarningsRecalculatesTheSumOfCompletedDonations()
    {
        $this->donation(1000);
        $this->donation(2550);
        $this->donation(400);
        $this->donation(700, DonationStatus::PENDING());

        $this->assertEquals(39.5, give_get_total_earnings(true));
    }

    /**
     * @since TBD
     */
    public function testEarningsByDateSumsTheDonationsOfTheMonth()
    {
        $this->donation(1000);
        $this->donation(2000);

        $earnings = give_get_earnings_by_date(null, (int)date('n'), (int)date('Y'));

        $this->assertEquals(30.0, $earnings);
    }

    /**
     * @since TBD
     */
    public function testPaymentUserIdReturnsTheUserOfTheDonor()
    {
        $userId = $this->factory()->user->create();
        $donor = Donor::factory()->create(['userId' => $userId]);
        $donation = $this->donation(1000, null, $donor);

        $this->assertSame($userId, give_get_payment_user_id($donation->id));
        $this->assertSame(0, give_get_payment_user_id(999999));
    }

    /**
     * @since TBD
     */
    public function testDonationIdByKeyFindsTheDonationByPurchaseKey()
    {
        $donation = $this->donation(1000);
        give()->payment_meta->update_meta($donation->id, '_give_payment_purchase_key', "key'with-quote");

        $this->assertSame($donation->id, (int)give_get_donation_id_by_key("key'with-quote"));
        $this->assertSame(0, give_get_donation_id_by_key('missing-key'));
    }

    /**
     * @since TBD
     */
    public function testPurchaseIdByTransactionIdFindsTheDonationByTransactionId()
    {
        $donation = $this->donation(1000);
        give()->payment_meta->update_meta($donation->id, '_give_payment_transaction_id', "txn'123");

        $this->assertSame($donation->id, (int)give_get_purchase_id_by_transaction_id("txn'123"));
        $this->assertSame(0, give_get_purchase_id_by_transaction_id('missing-txn'));
    }

    /**
     * @since TBD
     */
    public function testFormDonorCountCountsUniqueDonorsByDefault()
    {
        $donorOne = Donor::factory()->create();
        $donorTwo = Donor::factory()->create();
        $this->donation(1000, null, $donorOne);
        $this->donation(1000, null, $donorOne);
        $this->donation(1000, null, $donorTwo);
        $formId = $this->campaign->defaultForm()->id;

        $this->assertSame(2, give_get_form_donor_count($formId));
        $this->assertSame(3, give_get_form_donor_count($formId, ['unique' => false]));
    }

    /**
     * @since TBD
     */
    public function testFormDonorCountIsZeroForAFormWithoutDonations()
    {
        $this->donation(1000);

        $this->assertSame(0, give_get_form_donor_count(999999));
    }

    /**
     * @since TBD
     */
    public function testTotalPostTypeCountAcceptsOneOrSeveralPostTypes()
    {
        global $wpdb;

        $this->donation(1000);
        $this->donation(1000);

        $donations = (int)$wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'give_payment'");
        $both = (int)$wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'give_payment' OR post_type = 'give_forms'"
        );

        $this->assertSame($donations, give_get_total_post_type_count('give_payment'));
        $this->assertSame($both, give_get_total_post_type_count(['give_payment', 'give_forms']));
        $this->assertSame(0, give_get_total_post_type_count(''));
        $this->assertSame(0, give_get_total_post_type_count('no_such_post_type'));
    }

    /**
     * @since TBD
     */
    public function testCountPaymentsReturnsTheCountPerStatus()
    {
        $this->donation(1000);
        $this->donation(1000);
        $this->donation(1000, DonationStatus::PENDING());

        $counts = give_count_payments();

        $this->assertSame(2, (int)$counts->publish);
        $this->assertSame(1, (int)$counts->pending);
    }

    /**
     * @since TBD
     */
    public function testCountPaymentsCanLimitTheStatuses()
    {
        $this->donation(1000);
        $this->donation(1000, DonationStatus::PENDING());

        $counts = give_count_payments(['status' => ['publish', 'refunded']]);

        $this->assertSame(1, (int)$counts->publish);
        $this->assertSame(0, (int)$counts->pending);
    }

    /**
     * @since TBD
     */
    public function testCountPaymentsFiltersByForm()
    {
        $this->donation(1000);
        $this->donation(1000);

        $this->assertSame(2, (int)give_count_payments(['form_id' => $this->campaign->defaultForm()->id])->publish);
        $this->assertSame(0, (int)give_count_payments(['form_id' => 999999])->publish);
    }

    /**
     * Added after the fix: before it, a quote in a status broke the SQL.
     *
     * @since TBD
     */
    public function testCountPaymentsHandlesAStatusWithAQuote()
    {
        global $wpdb;

        $this->donation(1000);

        $counts = give_count_payments(['status' => ["pub'lish", "x') OR ('1'='1"]]);

        $this->assertSame('', $wpdb->last_error);
        $this->assertSame(0, array_sum(array_map('intval', get_object_vars($counts))));
    }

    /**
     * Added after the fix: before it, a status given as a plain string was a fatal error on PHP 8.
     *
     * @since TBD
     */
    public function testCountPaymentsAcceptsAStatusGivenAsAString()
    {
        $this->donation(1000);
        $this->donation(1000, DonationStatus::PENDING());

        $counts = give_count_payments(['status' => 'publish']);

        $this->assertSame(1, (int)$counts->publish);
        $this->assertSame(0, (int)$counts->pending);
    }

    /**
     * @since TBD
     */
    public function testCountPaymentsIgnoresAnInvalidOrder()
    {
        global $wpdb;

        $this->donation(1000);

        $counts = give_count_payments(['order' => 'DESC; DROP TABLE x']);

        $this->assertSame('', $wpdb->last_error);
        $this->assertSame(1, (int)$counts->publish);
    }

    /**
     * @since TBD
     */
    public function testBestSellingReturnsTheFormWithMostSalesFirst()
    {
        $topForm = $this->campaign->defaultForm()->id;
        $otherForm = Campaign::factory()->create()->defaultForm()->id;

        give()->form_meta->update_meta($otherForm, '_give_form_sales', 2);
        give()->form_meta->update_meta($topForm, '_give_form_sales', 7);

        $best = (new Give_Payment_Stats())->get_best_selling(10);

        $this->assertSame($topForm, (int)$best[0]->form_id);
        $this->assertEquals(7, $best[0]->sales);
        $this->assertSame($otherForm, (int)$best[1]->form_id);
        $this->assertCount(1, (new Give_Payment_Stats())->get_best_selling(1));
    }

    /**
     * @since TBD
     */
    private function donation(int $cents, ?DonationStatus $status = null, ?Donor $donor = null): Donation
    {
        $attributes = [
            'campaignId' => $this->campaign->id,
            'formId' => $this->campaign->defaultForm()->id,
            'status' => $status ?: DonationStatus::COMPLETE(),
            'gatewayId' => 'manual',
            'amount' => new Money($cents, 'USD'),
        ];

        if ($donor) {
            $attributes['donorId'] = $donor->id;
        }

        return Donation::factory()->create($attributes);
    }
}
