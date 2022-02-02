<?php

namespace Give\Donations\Repositories;

use Exception;
use Give\Donations\DataTransferObjects\DonationQueryData;
use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Log\Log;

class DonationRepository
{
    /**
     * Get Donation By ID
     *
     * @unreleased
     *
     * @param  int  $donationId
     *
     * @return Donation|null
     */
    public function getById($donationId)
    {
        $donation = DB::table('posts')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_parent', 'parentId']
            )
            ->attachMeta('give_donationmeta',
                'ID',
                'donation_id',
                ['_give_payment_total', 'amount'],
                ['_give_payment_currency', 'currency'],
                ['_give_payment_gateway', 'gateway'],
                ['_give_payment_donor_id', 'donorId'],
                ['_give_donor_billing_first_name', 'firstName'],
                ['_give_donor_billing_last_name', 'lastName'],
                ['_give_payment_donor_email', 'email'],
                ['subscription_id', 'subscriptionId']
            )
            ->where('ID', $donationId)
            ->get();

        if ( ! $donation) {
            return null;
        }

        return DonationQueryData::fromObject($donation)->toDonation();
    }

    /**
     * @param  int  $subscriptionId
     *
     * @return Donation[]
     */
    public function getBySubscriptionId($subscriptionId)
    {
        $donations = DB::table('posts')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_parent', 'parentId']
            )
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                ['_give_payment_total', 'amount'],
                ['_give_payment_currency', 'currency'],
                ['_give_payment_gateway', 'gateway'],
                ['_give_payment_donor_id', 'donorId'],
                ['_give_donor_billing_first_name', 'firstName'],
                ['_give_donor_billing_last_name', 'lastName'],
                ['_give_payment_donor_email', 'email'],
                ['subscription_id', 'subscriptionId']
            )
            ->leftJoin('give_donationmeta', 'ID', 'donationMeta.donation_id', 'donationMeta')
            ->where('post_type', 'give_payment')
            ->where('post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', $subscriptionId)
            ->orderBy('post_date', 'DESC')
            ->getAll();

        if ( ! $donations) {
            return [];
        }

        return array_map(static function ($donation) {
            return DonationQueryData::fromObject($donation)->toDonation();
        }, $donations);
    }

    /**
     * @param  int donorId
     *
     * @return Donation[]
     */
    public function getByDonorId($donorId)
    {
        $donations = DB::table('posts')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_parent', 'parentId']
            )
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                ['_give_payment_total', 'amount'],
                ['_give_payment_currency', 'currency'],
                ['_give_payment_gateway', 'gateway'],
                ['_give_payment_donor_id', 'donorId'],
                ['_give_donor_billing_first_name', 'firstName'],
                ['_give_donor_billing_last_name', 'lastName'],
                ['_give_payment_donor_email', 'email'],
                ['subscription_id', 'subscriptionId']
            )
            ->where('post_type', 'give_payment')
            ->whereIn('ID', function (QueryBuilder $builder) use ($donorId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', '_give_payment_donor_id')
                    ->where('meta_value', $donorId);
            })
            ->orderBy('post_date', 'DESC')
            ->getAll();

        if ( ! $donations) {
            return [];
        }

        return array_map(static function ($donation) {
            return DonationQueryData::fromObject($donation)->toDonation();
        }, $donations);
    }

    /**
     * @param  Donation  $donation
     *
     * @return Donation
     * @throws Exception|DatabaseQueryException
     */
    public function insert(Donation $donation)
    {
        $date = current_datetime()->format('Y-m-d H:i:s');

        DB::query('START TRANSACTION');

        try {
            DB::insert(DB::prefix('posts'), [
                'post_date' => $date,
                'post_date_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->status->getValue(),
                'post_type' => 'give_payment'
            ], null);

            $donationId = DB::last_insert_id();

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::insert(DB::prefix('give_donationmeta'), [
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
     *
     * @return Donation
     * @throws Exception|DatabaseQueryException
     */
    public function update(Donation $donation)
    {
        $date = current_datetime()->format('Y-m-d H:i:s');

        DB::query('START TRANSACTION');

        try {
            DB::update(DB::prefix('posts'), [
                'post_modified' => $date,
                'post_modified_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->status->getValue(),
                'post_type' => 'give_payment'
            ], ['id' => $donation->id]);

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::update(DB::prefix('give_donationmeta'), [
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
     *
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

    /**
     * @param int $id
     *
     * @return int
     */
    public function getSequentialId($id)
    {
        return (int)give()->seq_donation_number->get_serial_number($id);
    }
}

