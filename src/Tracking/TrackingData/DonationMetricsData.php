<?php

namespace Give\Tracking\TrackingData;

use Give\Framework\Database\DB;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Helpers\DonationStatuses;

/**
 * Class DonationMetricsData
 * @package Give\Tracking\TrackingData
 *
 * @since 2.10.2
 */
class DonationMetricsData implements TrackData
{
    /**
     * @var array
     */
    private $donationData = [];

    /**
     * @var int
     */
    private $donorCount = 0;

    /**
     * @var int
     */
    private $formCount = 0;

    /**
     * @inheritdoc
     * @return array|void
     */
    public function get()
    {
        $this->donorCount = $this->getDonorCount();
        $this->formCount = $this->getDonationFormCount();
        $this->donationData = (new DonationData())->get();

        $data = [
            'form_count' => $this->formCount,
            'donor_count' => $this->donorCount,
            'avg_donation_amount_by_donor' => $this->getAvgDonationAmountByDonor(),
        ];

        return array_merge($data, $this->donationData);
    }

    /**
     * Returns donor count which donated greater then zero
     *
     * @since 2.10.0
     * @return int
     */
    private function getDonorCount()
    {
        global $wpdb;

        $statues = DonationStatuses::getCompletedDonationsStatues(true);

        $donorCount = DB::get_var(
            "
			SELECT COUNT(DISTINCT dm.meta_value)
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
				INNER JOIN {$wpdb->donationmeta} as dm2 ON dm.donation_id = dm2.donation_id
				INNER JOIN {$wpdb->donors} as donor ON dm.meta_value = donor.id
			WHERE p.post_status IN ({$statues})
				AND p.post_type='give_payment'
				AND dm2.meta_key='_give_payment_mode'
				AND dm2.meta_value='live'
				AND dm.meta_key='_give_payment_donor_id'
				AND donor.purchase_value > 0
			"
        );

        return (int)$donorCount;
    }

    /**
     * Get average donation by donor.
     *
     * @since 2.10.0
     * @return int
     */
    private function getAvgDonationAmountByDonor()
    {
        $amount = 0;

        if ($this->donationData['revenue']) {
            $amount = (int)($this->donationData['revenue'] / $this->donorCount);
        }

        return $amount;
    }

    /**
     * Returns donation form count
     *
     * @since 2.10.0
     * @return int
     */
    private function getDonationFormCount()
    {
        global $wpdb;

        $statues = DonationStatuses::getCompletedDonationsStatues(true);

        $formCount = DB::get_var(
            "
			SELECT COUNT(DISTINCT dm.meta_value)
			FROM {$wpdb->donationmeta} as dm
				INNER JOIN {$wpdb->posts} as p ON dm.donation_id = p.ID
				INNER JOIN {$wpdb->donationmeta} as dm2 ON dm.donation_id = dm2.donation_id
			WHERE p.post_status IN ({$statues})
			  	AND p.post_type='give_payment'
				AND dm2.meta_key='_give_payment_mode'
				AND dm2.meta_value='live'
				AND dm.meta_key='_give_payment_form_id'
			"
        );

        return (int)$formCount;
    }
}
