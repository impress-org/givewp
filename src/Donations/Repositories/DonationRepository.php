<?php

namespace Give\Donations\Repositories;

use Give\Donations\DataTransferObjects\DonationPostData;
use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;

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
        $donationPost = get_post($donationId);

        return DonationPostData::fromPost($donationPost)->toDonation();
    }

    /**
     * @param  int  $subscriptionId
     *
     * @return object
     */
    public function getBySubscriptionId($subscriptionId)
    {
        return DB::table([$this->postsTable, 'posts'])
                 ->select(
                     ['posts.ID', 'id'],
                     ['posts.post_date', 'createdAt'],
                     ['posts.post_modified', 'updatedAt'],
                     ['posts.post_status', 'status'],
                     ['posts.post_parent', 'parentId']
                 )
                 ->attachMeta($this->donationMetaTable, 'posts.ID', 'donation_id',
                     ['_give_payment_total', 'amount'],
                     ['_give_payment_currency', 'paymentCurrency'],
                     ['_give_payment_gateway', 'paymentGateway'],
                     ['_give_payment_donor_id', 'donorId'],
                     ['_give_donor_billing_first_name', 'firstName'],
                     ['_give_donor_billing_last_name', 'lastName'],
                     ['_give_payment_donor_email', 'donorEmail']
                 )
                 ->leftJoin($this->donationMetaTable, 'posts.ID', 'donation_id', 'donationMeta')
                 ->where('posts.post_type', 'give_payment')
                 ->where('posts.post_status', 'give_subscription')
                 ->where('donationMeta.meta_key', 'subscription_id')
                 ->where('donationMeta.meta_value', $subscriptionId)
                 ->orderBy('posts.post_date', 'DESC')
                 ->get();
    }

    /**
     * @param  int donorId
     *
     * @return array|Donation[]
     */
    public function getByDonorId($donorId)
    {
        return DB::table('posts')
                 ->select(
                     ['ID', 'id'],
                     ['post_date', 'createdAt'],
                     ['post_modified', 'updatedAt'],
                     ['post_status', 'status'],
                     ['post_parent', 'parentId']
                 )
                 ->attachMeta($this->donationMetaTable, 'ID', 'donation_id',
                     ['_give_payment_total', 'amount'],
                     ['_give_payment_currency', 'paymentCurrency'],
                     ['_give_payment_gateway', 'paymentGateway'],
                     ['_give_payment_donor_id', 'donorId'],
                     ['_give_donor_billing_first_name', 'firstName'],
                     ['_give_donor_billing_last_name', 'lastName'],
                     ['_give_payment_donor_email', 'donorEmail']
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
    }
}

