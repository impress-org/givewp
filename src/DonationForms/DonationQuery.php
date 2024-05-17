<?php

namespace Give\DonationForms;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * An opinionated Query Builder for GiveWP donations and meta fields.
 *
 * @since 3.12.0
 *
 * Example usage:
 * (new DonationQuery)
 *     ->form(1816)
 *     ->between('2024-02-00', '2024-02-23')
 *     ->sumIntendedAmount();
 */
class DonationQuery extends QueryBuilder
{
    /**
     * @since 3.12.0
     */
    public function __construct()
    {
        $this->from('posts', 'donation');
    }

    /**
     * An opinionated join method for the donation meta table.
     * @since 3.12.0
     */
    public function joinMeta($key, $alias)
    {
        $this->join(function (JoinQueryBuilder $builder) use ($key, $alias) {
            $builder
                ->leftJoin('give_donationmeta', $alias)
                ->on('donation.ID', $alias . '.donation_id')
                ->andOn($alias . '.meta_key', $key, true);
        });
        return $this;
    }

    /**
     * An opinionated where method for the donation form ID meta field.
     * @since 3.12.0
     */
    public function form($formId)
    {
        $this->joinMeta('_give_payment_form_id', 'formId');
        $this->where('formId.meta_value', $formId);
        return $this;
    }


    /**
     * An opinionated where method for the multiple donation form IDs meta field.
     * @since 3.12.0
     */
    public function forms(array $formIds)
    {
        $this->joinMeta('_give_payment_form_id', 'formId');
        $this->whereIn('formId.meta_value', $formIds);
        return $this;
    }

    /**
     * An opinionated whereBetween method for the completed date meta field.
     * @since 3.12.0
     */
    public function between($startDate, $endDate)
    {
        // If the dates are empty or invalid, they will fallback to January 1st, 1970.
        // For the start date, this is exactly what we need, but for the end date, we should set it as the current date so that we have a correct date range.
        $startDate = date('Y-m-d H:i:s', strtotime($startDate));
        $endDate = empty($endDate)
            ? date('Y-m-d H:i:s')
            : date('Y-m-d H:i:s', strtotime($endDate));

        $this->joinMeta('_give_completed_date', 'completed');
        $this->whereBetween('completed.meta_value', $startDate, $endDate);
        return $this;
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     * @since 3.12.0
     * @return int|float
     */
    public function sumIntendedAmount()
    {
        $this->joinMeta('_give_payment_total', 'amount');
        $this->joinMeta('_give_fee_donation_amount', 'intendedAmount');
        return $this->sum(
            'COALESCE(intendedAmount.meta_value, amount.meta_value)'
        );
    }

    public function countDonors()
    {
        $this->joinMeta(DonationMetaKeys::DONOR_ID, 'donorId');
        return $this->count('DISTINCT donorId.meta_value');
    }
}
