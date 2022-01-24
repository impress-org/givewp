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
        global $wpdb;

        $this->postsTable        = "{$wpdb->prefix}posts";
        $this->donationMetaTable = "{$wpdb->prefix}give_donationmeta";
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
     * @return array|Donation[]
     */
    public function getBySubscriptionId($subscriptionId)
    {
        $builder = new QueryBuilder();

        $builder
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
            ->from($this->postsTable, 'posts')
            ->leftJoin($this->donationMetaTable, 'posts.ID', 'donation_id', 'donationMeta')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', $subscriptionId)
            ->orderBy('posts.post_date', 'DESC');

        // TODO: return DTO

        return DB::get_row($builder->getSQL());
    }

    /**
     * @param  int donorId
     *
     * @return array|Donation[]
     */
    public function getByDonorId($donorId)
    {
        $builder = new QueryBuilder();

        $builder
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
            ->from($this->postsTable)
            ->where('post_type', 'give_payment')
            ->whereIn( 'ID', function(QueryBuilder $builder) use ($donorId){
                $builder
                    ->select( 'donation_id')
                    ->from( $this->donationMetaTable)
                    ->where('meta_key', '_give_payment_donor_id')
                    ->where('meta_value', $donorId);
            })
            ->orderBy('post_date', 'DESC');

        // TODO: return DTO

        return DB::get_results($builder->getSQL());
    }
}

