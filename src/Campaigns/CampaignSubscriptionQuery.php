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
class CampaignSubscriptionQuery extends QueryBuilder
{
    /**
     * @since 4.0.0
     */
    public function __construct(Campaign $campaign)
    {
        $this->from('give_subscriptions', 'subscription');

        // Include only current payment "mode"
        $this->where('payment_mode', give_is_test_mode() ? 'test' : 'live');

        // Include only valid statuses
        $this->joinDonation('donation');
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);

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
            'created',
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        );
        return $query;
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     *
     * @since 4.0.0
     *
     * @return int|float
     */
    public function sumInitialAmount()
    {
        return (clone $this)->sum('initial_amount');
    }

    /**
     * @since 4.0.0
     */
    public function countDonations(): int
    {
        return (clone $this)->count('donation.ID');
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
    public function joinDonation($alias): self
    {
        $this->join(function (JoinQueryBuilder $builder) use ($alias) {
            $builder
                ->leftJoin('posts', $alias)
                ->on('subscription.parent_payment_id', $alias . '.ID');
        });
        $this->where($alias . '.post_type', 'give_payment');

        return $this;
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
                ->on('subscription.parent_payment_id', $alias . '.donation_id')
                ->andOn($alias . '.meta_key', $key, true);
        });
        return $this;
    }
}
