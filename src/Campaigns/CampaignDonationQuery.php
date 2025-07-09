<?php

namespace Give\Campaigns;

use DateTimeInterface;
use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 4.0.0
 */
class CampaignDonationQuery extends QueryBuilder
{
    /**
     * @since 4.0.0
     */
    public function __construct(Campaign $campaign)
    {
        $this->from('posts', 'donation');
        $this->where('post_type', 'give_payment');

        // Include only valid statuses
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);

        // Include only current payment "mode"
        $this->joinDonationMeta(DonationMetaKeys::MODE, 'paymentMode');
        $this->where('paymentMode.meta_value', give_is_test_mode() ? 'test' : 'live');

        // Include only forms associated with the Campaign.
        $this->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId');
        $this->where('campaignId.meta_value', $campaign->id);
    }

    /**
     * @since 4.0.0
     */
    public function between(DateTimeInterface $startDate, DateTimeInterface $endDate): self
    {
        $query = clone $this;
        $query->whereBetween(
            'donation.post_date',
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        );
        return $query;
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     *
     * @since 4.5.0 update to account for exchange rate
     * @since 4.0.0
     *
     * @return int|float
     */
    public function sumIntendedAmount()
    {
        $query = clone $this;
        $query->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount');
        $query->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered');
        $query->joinDonationMeta(DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate');
        return $query->sum(
            /**
             * The intended amount meta and the amount meta could either be 0 or NULL.
             * So we need to use the NULLIF function to treat the 0 values as NULL.
             * Then we coalesce the values to select the first non-NULL value.
             * @link https://github.com/impress-org/givewp/pull/7411
             */
            '(IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1)'
        );
    }

    /**
     * @since 4.0.0
     */
    public function countDonations(): int
    {
        $query = clone $this;
        return $query->count('donation.ID');
    }

    /**
     * @since 4.0.0
     */
    public function countDonors(): int
    {
        $query = clone $this;
        $query->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId');
        return $query->count('DISTINCT donorId.meta_value');
    }

    /**
     * @since 4.0.0
     */
    public function getOldestDonationDate()
    {
        $query = clone $this;
        $query->select('DATE(donation.post_date) as date_created');
        $query->orderBy('donation.post_date', 'ASC');
        $query->limit(1);
        $result = $query->get();

        if (!$result) {
            return null;
        }

        return $result->date_created;
    }

    /**
     * @since 4.5.0 update to account for exchange rate
     * @since 4.0.0
     */
    public function getDonationsByDate($groupBy = 'DATE'): array
    {
        $query = clone $this;

        $query->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount');
        $query->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered');
        $query->joinDonationMeta(DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate');
        $query->select(
            'SUM((IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1)) as amount'
        );

        $query->select('YEAR(donation.post_date) as year');
        $query->select('MONTH(donation.post_date) as month');
        $query->select('DAY(donation.post_date) as day');
        $query->select("DATE(donation.post_date) as date_created");

        if ($groupBy === 'DAY') {
            $query->groupBy('DATE(date_created)');
        } else if ($groupBy === 'MONTH') {
            $query->groupBy('YEAR(donation.post_date), MONTH(donation.post_date)');
        } elseif ($groupBy === 'YEAR') {
            $query->groupBy('YEAR(donation.post_date)');
        } else {
            $query->groupBy("$groupBy(donation.post_date)");
        }

        return $query->getAll();
    }

    /**
     * An opinionated join method for the donation meta table.
     * @since 4.0.0
     */
    public function joinDonationMeta($key, $alias): self
    {
        $this->join(function (JoinQueryBuilder $builder) use ($key, $alias) {
            $builder
                ->leftJoin('give_donationmeta', $alias)
                ->on('donation.ID', $alias . '.donation_id')
                ->andOn($alias . '.meta_key', $key, true);
        });
        return $this;
    }
}
