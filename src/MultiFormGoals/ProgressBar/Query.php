<?php

namespace Give\MultiFormGoals\ProgressBar;

use wpdb;

/**
 * Get the Total, Count, and Average of the payment totals for published donations of a given set of forms.
 */
class Query
{

    /** @var array */
    protected $formIDs;

    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var array $formIDs
     */
    public function __construct($formIDs)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->formIDs = $formIDs;
    }

    /**
     * @since 3.14.0 Consider the donation mode (test or live) instead of querying both modes together
     * @return string
     */
    public function getSQL()
    {
        global $wpdb;
        $mode = give_is_test_mode() ? 'test' : 'live';
        $sql = "
            SELECT
                sum( revenue.amount ) as total,
                count( payment.ID ) as count
            FROM {$wpdb->posts} as payment
                JOIN {$wpdb->give_revenue} as revenue
                    ON revenue.donation_id = payment.ID
                JOIN {$wpdb->paymentmeta} paymentMode
                    ON payment.ID = paymentMode.donation_id AND paymentMode.meta_key = '_give_payment_mode'
            WHERE
                payment.post_type = 'give_payment'
                AND
                payment.post_status IN ( 'publish', 'give_subscription' )
                AND
                paymentMode.meta_value = '{$mode}'
        ";

        if ( ! empty($this->formIDs)) {
            $sql .= '
                AND
                revenue.form_id IN ( ' . $this->getFormsString() . ' )
            ';
        }

        return $sql;
    }

    /**
     * @return string
     */
    protected function getFormsString()
    {
        return implode(',', $this->formIDs);
    }

    /**
     * @return stdClass
     */
    public function getResults()
    {
        $sql = $this->getSQL();

        return $this->wpdb->get_row($sql);
    }
}
