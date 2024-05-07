<?php

namespace Give\DonationForms;

use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
class SubscriptionQuery extends QueryBuilder
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->from('give_subscriptions');
    }

    /**
     * @unreleased
     */
    public function form($formId)
    {
        $this->where('product_id', $formId);
        return $this;
    }


    /**
     * @unreleased
     */
    public function forms(array $formIds)
    {
        $this->whereIn('product_id', $formIds);
        return $this;
    }

    /**
     * @unreleased
     */
    public function between($startDate, $endDate)
    {
        $this->whereBetween(
            'created',
            date('Y-m-d H:i:s', strtotime($startDate)),
            date('Y-m-d H:i:s', strtotime($endDate))
        );
        return $this;
    }

    /**
     * @unreleased
     */
    public function sumInitialAmount()
    {
        return $this->sum('initial_amount');
    }

    /**
     * @unreleased
     */
    public function countDonors()
    {
        return $this->count('DISTINCT customer_id');
    }
}
