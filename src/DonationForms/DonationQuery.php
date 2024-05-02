<?php

namespace Give\DonationForms;

use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * An opinionated Query Builder for GiveWP donations and meta fields.
 *
 * @unreleased
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
     * @unreleased
     */
    public function __construct()
    {
        $this->from('posts', 'donation');
    }

    /**
     * An opinionated join method for the donation meta table.
     * @unreleased
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
     * @unreleased
     */
    public function form($formId)
    {
        $this->joinMeta('_give_payment_form_id', 'formId');
        $this->where('formId.meta_value', $formId);
        return $this;
    }

    /**
     * An opinionated whereBetween method for the completed date meta field.
     * @unreleased
     */
    public function between($startDate, $endDate)
    {
        $this->joinMeta('_give_completed_date', 'completed');
        $this->whereBetween(
            'completed.meta_value',
            date('Y-m-d H:i:s', strtotime($startDate)),
            date('Y-m-d H:i:s', strtotime($endDate))
        );
        return $this;
    }

    /**
     * Returns a calculated sum of the intended amounts (without recovered fees) for the donations.
     * @unreleased
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
}
