<?php

namespace Give\Donors;

use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Donors\ValueObjects\DonorType;

/**
 * @since 4.4.0
 */
class DonorStatisticsQuery extends QueryBuilder
{
    /**
     * @since 4.4.0
     */
    private Donor $donor;

    /**
     * @since 4.4.0
     */
    public function __construct(Donor $donor, $mode = '')
    {
        $this->donor = $donor;

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
        $this->joinDonationMeta(DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate');
    }

    /**
     * @since 4.4.0
     */
    public function filterByCampaign(Campaign $campaign): self
    {
        $query = clone $this;

        $query->joinDonationMeta(DonationMetaKeys::CAMPAIGN_ID, 'campaignId');
        $query->where('campaignId.meta_value', $campaign->id);

        return $query;
    }

    /**
     * @since 4.5.0 update to account for exchange rate
     * @since 4.4.0
     *
     * @return int|float
     */
    public function getLifetimeDonationsAmount()
    {
        $query = clone $this;

        return $query->sum(
            '(IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1)'
        );
    }

    /**
     * @since 4.5.0 update to account for exchange rate
     * @since 4.4.0
     */
    public function getHighestDonationAmount()
    {
        $query = clone $this;

        $query->select('(IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1) as highestDonationAmount');
        $query->orderBy('CAST(amount.meta_value AS DECIMAL)', 'DESC');
        $query->limit(1);
        $result = $query->get();

        if ( ! $result) {
            return null;
        }

        return (float)$result->highestDonationAmount;
    }

    /**
     * @since 4.5.0 update to account for exchange rate
     * @since 4.4.0
     */
    public function getAverageDonationAmount()
    {
        $query = clone $this;

        $donationsCount = $this->getDonationsCount();
        $lifetimeDonationsAmount = $query->getLifetimeDonationsAmount();

        return $donationsCount > 0 ? $lifetimeDonationsAmount / $donationsCount : $lifetimeDonationsAmount;
    }

    /**
     * @since 4.4.0
     */
    public function getDonationsCount(): int
    {
        $query = clone $this;

        return $query->count('donation.ID');
    }


    /**
     * @since 4.5.0 update to account for exchange rate
     * @since 4.4.0
     */
    public function getFirstDonation()
    {
        $query = clone $this;
        $query->select(
            'donation.post_date',
            '(IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1) as amount'
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
     * @since 4.5.0 update to account for exchange rate
     * @since 4.4.0
     */
    public function getLastDonation()
    {
        $query = clone $this;
        $query->select(
            'donation.post_date',
            '(IFNULL(amount.meta_value, 0) - IFNULL(feeAmountRecovered.meta_value, 0)) / IFNULL(exchangeRate.meta_value, 1) as amount'
        );
        $query->orderBy('post_date', 'DESC');
        $query->limit(1);
        $result = $query->get();

        if (!$result) {
            return null;
        }

        return [
            'amount' => (float)$result->amount,
            'date' => date('Y-m-d H:i:s', strtotime($result->post_date)),
        ];
    }

    /**
     * @since 4.10.0 Updated return value
     * @since 4.4.0
     */
    public function getDonorType()
    {
        $donorRepository = give(DonorRepository::class);
        $donorType = $donorRepository->getDonorType($this->donor->id);

        if (!$donorType) {
            return null;
        }

        return $donorType->label();
    }

    /**
     * @since 4.4.0
     */
    public function preferredPaymentMethod(): string
    {
        $query = clone $this;
        $query->joinDonationMeta(DonationMetaKeys::GATEWAY, 'gateway');
        $query->select('gateway.meta_value as gateway');
        $query->groupBy('gateway.meta_value');
        $query->orderBy('COUNT(gateway.meta_value)', 'DESC');
        $query->limit(1);
        $result = $query->get();

        if (!$result) {
            return '';
        }

        return give_get_gateway_checkout_label($result->gateway) ?? $result->gateway;
    }

    /**
     * @since 4.4.0
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
