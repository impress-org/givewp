<?php

namespace Give\Campaigns;

use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
class CampaignDonationQuery extends QueryBuilder
{
    /**
     * @unreleased
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
        $this->joinDonationMeta(DonationMetaKeys::FORM_ID, 'formId');
        $this->join(function (JoinQueryBuilder $builder) {
            $builder->leftJoin('give_campaign_forms', 'campaign_forms')
                    ->on('campaign_forms.form_id', 'formId.meta_value');
        });
        $this->where('campaign_forms.campaign_id', $campaign->id);
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     *
     * @unreleased
     *
     * @return int|float
     */
    public function sumIntendedAmount()
    {
        $this->joinDonationMeta(DonationMetaKeys::AMOUNT, 'amount');
        $this->joinDonationMeta('_give_fee_donation_amount', 'intendedAmount');
        return $this->sum(
            /**
             * The intended amount meta and the amount meta could either be 0 or NULL.
             * So we need to use the NULLIF function to treat the 0 values as NULL.
             * Then we coalesce the values to select the first non-NULL value.
             * @link https://github.com/impress-org/givewp/pull/7411
             */
            'COALESCE(NULLIF(intendedAmount.meta_value,0), NULLIF(amount.meta_value,0), 0)'
        );
    }

    /**
     * @unreleased
     */
    public function countDonations(): int
    {
        return $this->count('donation.ID');
    }

    /**
     * @unreleased
     */
    public function countDonors(): int
    {
        $this->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorId');
        return $this->count('DISTINCT donorId.meta_value');
    }

    /**
     * An opinionated join method for the donation meta table.
     * @unreleased
     */
    protected function joinDonationMeta($key, $alias): self
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
