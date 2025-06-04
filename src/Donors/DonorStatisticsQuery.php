<?php

namespace Give\Donors;

use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Donors\ValueObjects\DonorType;
use Give\Donations\Models\Donation;

/**
 * @unreleased
 */
class DonorStatisticsQuery extends QueryBuilder
{
    /**
     * @unreleased
     */
    public function __construct(Donor $donor, $mode = '')
    {
        $this->from('posts', 'donation');
        $this->where('post_type', 'give_payment');

        // Include only valid statuses
        $this->whereIn('donation.post_status', ['publish', 'give_subscription']);

        // Include only current payment "mode"
        if (empty($mode)) {
            $mode = give_is_test_mode() ? 'test' : 'live';
        }
        $this->joinDonationMeta(DonationMetaKeys::MODE, 'paymentMode');
        $this->where('paymentMode.meta_value', $mode);

        // Include only donations associated with the Donor.
        $this->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId');
        $this->where('donorId.meta_value', $donor->id);

        $this->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount');
        $this->joinDonationMeta(DonationMetaKeys::FEE_AMOUNT_RECOVERED, 'feeAmountRecovered');
    }

    /**
     * @unreleased
     */
    public function filterByCampaign(Campaign $campaign): self
    {
        $query = clone $this;

        $query->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId');
        $query->where('campaignId.meta_value', $campaign->id);

        return $query;
    }

    /**
     * @unreleased
     *
     * @return int|float
     */
    public function getLifetimeDonationsAmount()
    {
        $query = clone $this;

        return $query->sum(
            'IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)'
        );
    }

    /**
     * @unreleased
     */
    public function getHighestDonationAmount()
    {
        $query = clone $this;

        $query->select('IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0) as highestDonationAmount');
        $query->orderBy('CAST(amount.meta_value AS DECIMAL)', 'DESC');
        $query->limit(1);
        $result = $query->get();

        if ( ! $result) {
            return null;
        }

        return (float)$result->highestDonationAmount;
    }

    /**
     * @unreleased
     */
    public function getAverageDonationAmount()
    {
        $query = clone $this;

        $donationsCount = $this->getDonationsCount();
        $lifetimeDonationsAmount = $query->getLifetimeDonationsAmount();

        return $donationsCount > 0 ? $lifetimeDonationsAmount / $donationsCount : $lifetimeDonationsAmount;
    }

    /**
     * @unreleased
     */
    public function getDonationsCount(): int
    {
        $query = clone $this;

        return $query->count('donation.ID');
    }


    /**
     * @unreleased
     */
    public function getFirstDonation()
    {
        $query = clone $this;
        $query->select(
            'donation.post_date',
            'IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0) as amount'
        );
        $query->orderBy('post_date', 'ASC');
        $query->limit(1);
        $result = $query->get();

        if (!$result) {
            return null;
        }

        return [
            'amount' => (float)$result->amount,
            'date' => date('Y-m-d H:i:s', strtotime($result->post_date))
        ];
    }

    /**
     * @unreleased
     */
    public function getLastContribution()
    {
        $query = clone $this;
        $query->select('donation.post_date');
        $query->orderBy('post_date', 'DESC');
        $query->limit(1);
        $result = $query->get();

        if (!$result) {
            return null;
        }

        return human_time_diff(strtotime($result->post_date), current_time('timestamp')) . ' ago';
    }

    /**
     * @unreleased
     *
     * @return DonorType|null
     */
    public function getDonorType()
    {
        // Create a new query for checking subscription status
        $subscriptionQuery = clone $this;
        $subscriptionQuery->where('donation.post_status', 'give_subscription');
        $hasSubscription = $subscriptionQuery->count('donation.ID') > 0;

        if ($hasSubscription) {
            return DonorType::SUBSCRIBER();
        }

        // Check total number of donations using the original query
        $totalDonations = $this->getDonationsCount();

        if ($totalDonations === 0) {
            return DonorType::NEW();
        }

        return $totalDonations > 1 ? DonorType::REPEAT() : DonorType::SINGLE();
    }

    /**
     * @unreleased
     */
    public function getPreferredGivingType(): string
    {
        $donorType = $this->getDonorType();
        return $donorType && $donorType->isSubscriber() ? 'recurring' : 'single';
    }

    /**
     * @unreleased
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
