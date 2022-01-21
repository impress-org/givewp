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

        $builder->tables([
            'posts'        => $this->postsTable,
            'donationMeta' => $this->donationMetaTable
        ]);

        $builder->select([
            ['posts.ID', 'id'],
            ['posts.post_date', 'createdAt'],
            ['posts.post_modified', 'updatedAt'],
            ['posts.post_status', 'status'],
            ['posts.post_parent', 'parentId'],
        ]);

        $builder
            ->from('posts')
            ->join('donationMeta', 'ID', 'donation_id')
            ->where('posts.post_type', 'give_payment')
            ->where('posts.post_status', 'give_subscription')
            ->where('donationMeta.meta_key', 'subscription_id')
            ->where('donationMeta.meta_value', $subscriptionId)
            ->orderBy('posts.post_date', 'DESC');

        // TODO: return DTO

        return DB::get_results($builder->getSQL());
    }

    /**
     * @param  int donorId
     *
     * @return array|Donation[]
     */
    public function getByDonorId($donorId)
    {
        global $wpdb;

        $donationIds = $wpdb->get_col(
            "SELECT donation_id
                    FROM {$wpdb->prefix}give_donationmeta
                    WHERE meta_key = '_give_payment_donor_id'
                    AND meta_value = '$donorId'"
        );

        $posts = get_posts([
            'include'   => $donationIds,
            'post_type' => 'give_payment',
            'orderby'   => 'post_date',
        ]);

        return array_map(static function ($post) {
            return DonationPostData::fromPost($post)->toDonation();
        }, $posts);
    }
}

