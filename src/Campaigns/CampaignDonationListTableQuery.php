<?php

namespace Give\Campaigns;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class used for eager loading the number of donors, donations and revenue amounts for a range of campaign Ids
 *
 * @unreleased
 */
class CampaignDonationListTableQuery extends QueryBuilder
{
    private function __construct(array $campaigns)
    {
        $this->select('campaignId.meta_value as campaign_id');
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);
        $this->whereIn('campaignId.meta_value', $campaigns);
        $this->groupBy('campaign_id');
    }


    /**
     * Donations query for campaigns
     *
     * @param int[] $campaigns - campaign ids
     *
     * @return CampaignDonationListTableQuery
     */
    public static function donations(array $campaigns): CampaignDonationListTableQuery
    {
        return (new self($campaigns))
            ->from('posts', 'donation')
            ->where('post_type', 'give_payment')
            ->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId')
            ->joinDonationMeta(DonationMetaKeys::MODE, 'paymentMode')
            ->where('paymentMode.meta_value', give_is_test_mode() ? 'test' : 'live');
    }

    /**
     * Subscriptions query for campaigns
     *
     * @param int[] $campaigns - campaign ids
     *
     * @return CampaignDonationListTableQuery
     */
    public static function subscriptions(array $campaigns): CampaignDonationListTableQuery
    {
        return (new self($campaigns))
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
     * @unreleased
     *
     * @return array|object|null
     */
    public function collectIntendedAmounts()
    {
        $query = clone $this;
        $query->select('SUM(COALESCE(NULLIF(intendedAmount.meta_value,0), NULLIF(amount.meta_value,0))) as sum');
        $query->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount');
        $query->joinDonationMeta('_give_fee_donation_amount', 'intendedAmount');

        return $query->getAll(ARRAY_A);
    }

    /**
     * Returns a calculated sum of the initial amounts
     *
     * @unreleased
     *
     * @return array|object|null
     */
    public function collectInitialAmounts()
    {
        $query = clone $this;
        $query->select('SUM(initial_amount) as sum');

        return $query->getAll(ARRAY_A);
    }

    /**
     * @unreleased
     */
    public function collectDonations()
    {
        $query = clone $this;
        $query->select('COUNT(donation.ID) as count');

        return $query->getAll(ARRAY_A);
    }

    /**
     * @unreleased
     */
    public function collectDonors()
    {
        $query = clone $this;
        $query->select('COUNT(DISTINCT donorId.meta_value) as count');
        $query->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId');

        return $query->getAll(ARRAY_A);
    }

    /**
     * An opinionated join method for the donation meta table.
     * @unreleased
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
