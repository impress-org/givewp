<?php

namespace Give\DonationForms;

use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 3.12.0
 */
class SubscriptionQuery extends QueryBuilder
{
    /**
     * @since 3.12.0
     */
    public function __construct()
    {
        $this->from('give_subscriptions');
    }

    /**
     * @since 3.12.0
     */
    public function form($formId)
    {
        $this->where('product_id', $formId);
        return $this;
    }


    /**
     * @since 3.12.0
     */
    public function forms(array $formIds)
    {
        $this->whereIn('product_id', $formIds);
        return $this;
    }

    /**
     * @since TBD Use gmdate() instead of date().
     * @since 3.12.0
     */
    public function between($startDate, $endDate)
    {
        $this->whereBetween(
            'created',
            gmdate('Y-m-d H:i:s', strtotime($startDate)),
            gmdate('Y-m-d H:i:s', strtotime($endDate))
        );
        return $this;
    }

    /**
     * @since 3.12.0
     */
    public function sumInitialAmount()
    {
        return $this->sum('initial_amount');
    }

    /**
     * @since 3.12.0
     */
    public function countDonors()
    {
        return $this->count('DISTINCT customer_id');
    }
}
