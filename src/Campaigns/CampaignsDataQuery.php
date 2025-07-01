<?php

namespace Give\Campaigns;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class used for loading the number of donors, donations and revenue amounts for multiple campaigns
 *
 * @since 4.0.0
 */
class CampaignsDataQuery extends QueryBuilder
{
    /**
     * @since 4.0.0
     *
     * @param int[] $campaignIds
     */
    private function __construct(array $campaignIds)
    {
        $this->select('campaignId.meta_value as campaign_id');
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);
        $this->whereIn('campaignId.meta_value', $campaignIds);
        $this->groupBy('campaign_id');
    }


    /**
     * Donations query for campaigns
     *
     * @param int[] $campaignIds - campaign ids
     *
     * @return CampaignsDataQuery
     */
    public static function donations(array $campaignIds): CampaignsDataQuery
    {
        return (new self($campaignIds))
            ->from('posts', 'donation')
            ->where('post_type', 'give_payment')
            ->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId')
            ->joinDonationMeta(DonationMetaKeys::MODE, 'paymentMode')
            ->where('paymentMode.meta_value', give_is_test_mode() ? 'test' : 'live');
    }

    /**
     * Subscriptions query for campaigns
     *
     * @param int[] $campaignIds - campaign ids
     *
     * @return CampaignsDataQuery
     */
    public static function subscriptions(array $campaignIds): CampaignsDataQuery
    {
        return (new self($campaignIds))
            ->from('give_subscriptions', 'subscription')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('posts', 'donation')
                    ->on('subscription.parent_payment_id', 'donation.ID');
            })
            ->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId')
            ->where('payment_mode', give_is_test_mode() ? 'test' : 'live')
            ->where('donation.post_type', 'give_payment');
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     *
     * @since 4.0.0
     *
     * @return array|object|null
     */
    public function collectIntendedAmounts()
    {
        return (clone $this)
            ->select('SUM((IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1)) as sum')
            ->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount')
            ->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered')
            ->joinDonationMeta(DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate')
            ->getAll(ARRAY_A);
    }

    /**
     * Returns a calculated sum of the initial amounts
     *
     * @since 4.0.0
     *
     * @return array|object|null
     */
    public function collectInitialAmounts()
    {
        return (clone $this)
            ->select('SUM(initial_amount) as sum')
            ->getAll(ARRAY_A);
    }

    /**
     * @since 4.0.0
     */
    public function collectDonations()
    {
        return (clone $this)
            ->select('COUNT(donation.ID) as count')
            ->getAll(ARRAY_A);
    }

    /**
     * @since 4.0.0
     */
    public function collectDonors()
    {
        return (clone $this)
            ->select('COUNT(DISTINCT donorId.meta_value) as count')
            ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId')
            ->getAll(ARRAY_A);
    }

    /**
     * An opinionated join method for the donation meta table.
     * @since 4.0.0
     */
    private function joinDonationMeta($key, $alias): self
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
