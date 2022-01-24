<?php

namespace Give\Donations\Repositories;

use Exception;
use Give\Donations\DataTransferObjects\DonationPostData;
use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Log\Log;

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

        $donationIds = DB::get_col(
            "SELECT donation_id
                    FROM {$wpdb->prefix}give_donationmeta
                    WHERE meta_key = 'subscription_id'
                    AND meta_value = '$subscriptionId'"
        );

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

        $donationIds = DB::get_col(
            "SELECT donation_id
                    FROM {$wpdb->prefix}give_donationmeta
                    WHERE meta_key = '_give_payment_donor_id'
                    AND meta_value = '$donorId'"
        );

        $posts = get_posts([
            'include' => $donationIds,
            'post_type' => 'give_payment',
            'orderby' => 'post_date',
        ]);

        return array_map(static function ($post) {
            return DonationPostData::fromPost($post)->toDonation();
        }, $posts);
    }

    /**
     * @param  Donation  $donation
     * @return Donation
     * @throws Exception|DatabaseQueryException
     */
    public function insert(Donation $donation)
    {
        global $wpdb;

        $date = current_datetime()->format('Y-m-d H:i:s');

        DB::query('START TRANSACTION');

        try {
            DB::insert($wpdb->posts, [
                'post_date' => $date,
                'post_date_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->getStatus(),
                'post_type' => 'give_payment'
            ], null);

            $donationId = DB::last_insert_id();

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::insert($wpdb->donationmeta, [
                    'donation_id' => $donationId,
                    'meta_key' => $metaKey,
                    'meta_value' => $metaValue,
                ], null);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation');

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        return Donation::find($donationId);
    }

    /**
     * @param  Donation  $donation
     * @return Donation
     * @throws Exception|DatabaseQueryException
     */
    public function update(Donation $donation)
    {
        global $wpdb;

        $date = current_datetime()->format('Y-m-d H:i:s');

        DB::query('START TRANSACTION');

        try {
            DB::update($wpdb->posts, [
                'post_modified' => $date,
                'post_modified_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->getStatus(),
                'post_type' => $donation->getStatus()
            ], ['id' => $donation->id]);

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::update($wpdb->donationmeta, [
                    'meta_value' => $metaValue,
                ], [
                    'donation_id' => $donation->id,
                    'meta_key' => $metaKey
                ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation');

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        return $donation;
    }

    /**
     * @param  Donation  $donation
     * @return array
     */
    public function getMeta(Donation $donation)
    {
        return [
            '_give_payment_total' => $donation->amount,
            '_give_payment_currency' => $donation->currency,
            '_give_payment_gateway' => $donation->gateway,
            '_give_payment_donor_id' => $donation->donorId,
            '_give_donor_billing_first_name' => $donation->firstName,
            '_give_donor_billing_last_name' => $donation->lastName,
            '_give_payment_donor_email' => $donation->email,
            'subscription_id' => $donation->subscriptionId
        ];
    }
}

