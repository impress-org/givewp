<?php

namespace Give\Donations\Repositories;

use Give\Donations\DataTransferObjects\DonationPostData;
use Give\Donations\Models\Donation;

class DonationRepository
{
    /**
     * Get Donation By ID
     *
     * @unreleased
     *
     * @param  int  $donationId
     * @return Donation
     */
    public function getById($donationId)
    {
        $donationPost = get_post($donationId);

        return DonationPostData::fromPost($donationPost)->toDonation();
    }

    /**
     * @param  int  $subscriptionId
     * @return array|Donation[]
     */
    public function getBySubscriptionId($subscriptionId)
    {
        global $wpdb;

        $query = $wpdb->get_results(
            "SELECT donation_id
                    FROM {$wpdb->prefix}give_donationmeta
                    WHERE meta_key = 'subscription_id'
                    AND meta_value = '$subscriptionId'"
        );

        $donationIds = array_column($query, 'donation_id');

        $posts = get_posts([
            'include' => $donationIds,
            'post_type' => 'give_payment',
            'post_status' => 'give_subscription',
            'orderby' => 'post_date',
        ]);

        return array_map(static function ($post) {
            return DonationPostData::fromPost($post)->toDonation();
        }, $posts);
    }

    /**
     * @param  int donorId
     * @return array|Donation[]
     */
    public function getByDonorId($donorId)
    {
        global $wpdb;

        $query = $wpdb->get_results(
            "SELECT donation_id
                    FROM {$wpdb->prefix}give_donationmeta
                    WHERE meta_key = '_give_payment_donor_id'
                    AND meta_value = '$donorId'"
        );

        $donationIds = array_column($query, 'donation_id');

        $posts = get_posts([
            'include' => $donationIds,
            'post_type' => 'give_payment',
            'orderby' => 'post_date',
        ]);

        return array_map(static function ($post) {
            return DonationPostData::fromPost($post)->toDonation();
        }, $posts);
    }
}

