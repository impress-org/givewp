<?php

namespace Give\Tracking\TrackingData;

use Give\Framework\Database\DB;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Helpers\DonationStatuses;

/**
 * Class DonationData
 *
 * Represents donation data.
 *
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class DonationData implements TrackData
{
    private $donationStatuses;

    /**
     * DonationData constructor.
     */
    public function __construct()
    {
        $this->donationStatuses = DonationStatuses::getCompletedDonationsStatues(true);
    }

    /**
     * @inheritdoc
     * @return array|void
     */
    public function get()
    {
        return [
            'first_donation_date' => $this->getFirstDonationDate(),
            'last_donation_date' => $this->getLastDonationDate(),
            'revenue' => $this->getRevenueTillNow(),
        ];
    }

    /**
     * Get first donation date.
     *
     * @since 2.10.0
     * @return string
     */
    private function getFirstDonationDate()
    {
        global $wpdb;

        $date = DB::get_var(
            "
			SELECT post_date_gmt
			FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm ON p.id=dm.donation_id
			WHERE post_status IN ({$this->donationStatuses})
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			ORDER BY post_date_gmt ASC
			LIMIT 1
			"
        );

        return $date ? strtotime($date) : '';
    }

    /**
     * Get last donation date.
     *
     * @since 2.10.0
     * @return string
     */
    private function getLastDonationDate()
    {
        global $wpdb;

        $date = DB::get_var(
            "
			SELECT post_date_gmt
			FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->donationmeta} as dm ON p.id=dm.donation_id
			WHERE post_status IN ({$this->donationStatuses})
				AND dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			ORDER BY post_date_gmt DESC
			LIMIT 1
			"
        );

        return $date ? strtotime($date) : '';
    }

    /**
     * Returns revenue till current date.
     *
     * @since 2.10.0
     * @return int
     */
    public function getRevenueTillNow()
    {
        global $wpdb;

        $result = (int)DB::get_var(
            "
			SELECT SUM(r.amount)
			FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->donationmeta} as dm ON r.donation_id=dm.donation_id
			WHERE dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
        );

        return $result ?: 0;
    }
}
