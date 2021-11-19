<?php

namespace Give\Tracking\TrackingData;

use Give\Framework\Database\DB;

/**
 * Class AllActiveDonationFormsData
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.0
 */
class ActiveDonationFormsData extends DonationFormsData
{
    /**
     * Set form ids.
     *
     * @since 2.10.0
     *
     * @return DonationFormsData
     */
    protected function setFormIds()
    {
        global $wpdb;

        $this->formIds = DB::get_col(
            "
			SELECT DISTINCT r.form_id
			FROM {$wpdb->give_revenue} as r
				INNER JOIN {$wpdb->donationmeta} as dm ON r.donation_id = dm.donation_id
			WHERE dm.meta_key='_give_payment_mode'
				AND dm.meta_value='live'
			"
        );

        return $this;
    }
}
