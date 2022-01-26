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
     * @var string
     */
    private $postsTable;

    /**
     * @var string
     */
    private $donationMetaTable;

    public function __construct()
    {
        $this->postsTable = DB::prefix('posts');
        $this->donationMetaTable = DB::prefix('give_donationmeta');
    }

    /**
     * Get Donation By ID
     *
     * @unreleased
     *
     * @param  int  $donationId
     *
     * @return Donation
     */
    public function getById($donationId)
    {
        $donation = DB::table($this->postsTable)
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_parent', 'parentId']
            )
            ->attachMeta($this->donationMetaTable,
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

        return DonationQueryData::fromObject($donation)->toDonation();
    }

    /**
     * @param  int  $subscriptionId
     *
     * @return Donation[]
     */
    public function getBySubscriptionId($subscriptionId)
    {
        $donations = DB::table($this->postsTable, 'posts')
            ->select(
                ['posts.ID', 'id'],
                ['posts.post_date', 'createdAt'],
                ['posts.post_modified', 'updatedAt'],
                ['posts.post_status', 'status'],
                ['posts.post_parent', 'parentId']
            )
            ->attachMeta(
                $this->donationMetaTable,
                'posts.ID',
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
            ->leftJoin($this->donationMetaTable, 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', $subscriptionId)
            ->orderBy('posts.post_date', 'DESC')
            ->getAll();


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
        $donations = DB::table($this->postsTable)
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_parent', 'parentId']
            )
            ->attachMeta(
                $this->donationMetaTable,
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
                    ->from($this->donationMetaTable)
                    ->where('meta_key', '_give_payment_donor_id')
                    ->where('meta_value', $donorId);
            })
            ->orderBy('post_date', 'DESC')
            ->getAll();

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
            DB::insert($this->postsTable, [
                'post_date' => $date,
                'post_date_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->status,
                'post_type' => 'give_payment'
            ], null);

            $donationId = DB::last_insert_id();

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::insert($this->donationMetaTable, [
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
            DB::update($this->postsTable, [
                'post_modified' => $date,
                'post_modified_gmt' => get_gmt_from_date($date),
                'post_status' => $donation->status,
                'post_type' => $donation->status
            ], ['id' => $donation->id]);

            foreach ($donation->getMeta() as $metaKey => $metaValue) {
                DB::update($this->donationMetaTable, [
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
}

