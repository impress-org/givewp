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
     * @since TBD Return a prepared query.
     * @since 4.14.0 Replace {$wpdb->paymentmeta} with {$wpdb->donationmeta}
     * @since 3.14.0 Consider the donation mode (test or live) instead of querying both modes together
     * @return string
     */
    public function getSQL()
    {
        global $wpdb;
        $mode = give_is_test_mode() ? 'test' : 'live';
        $sql = $this->wpdb->prepare("
            SELECT
                sum( revenue.amount ) as total,
                count( payment.ID ) as count
            FROM {$wpdb->posts} as payment
                JOIN {$wpdb->give_revenue} as revenue
                    ON revenue.donation_id = payment.ID
                JOIN {$wpdb->donationmeta} paymentMode
                    ON payment.ID = paymentMode.donation_id AND paymentMode.meta_key = '_give_payment_mode'
            WHERE
                payment.post_type = 'give_payment'
                AND
                payment.post_status IN ( 'publish', 'give_subscription' )
                AND
                paymentMode.meta_value = %s
        ", $mode);

        if (!empty($this->formIDs)) {
            $sql .= $this->wpdb->prepare(
                '
                AND
                revenue.form_id IN ( ' . implode(',', array_fill(0, count($this->formIDs), '%d')) . ' )
            ',
                $this->formIDs
            );
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
     * @since TBD Document why the query is safe.
     *
     * @return stdClass
     */
    public function getResults()
    {
        $sql = $this->getSQL();

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- getSQL() returns a prepared query.
        return $this->wpdb->get_row($sql);
    }
}
