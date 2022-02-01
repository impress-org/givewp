<?php

namespace Give\Subscriptions\Repositories;

use Give\Framework\Database\DB;
use Give\Subscriptions\DataTransferObjects\SubscriptionQueryData;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionRepository
{
    /**
     * @unreleased
     *
     * @param  int  $subscriptionId
     * @return Subscription
     */
    public function getById($subscriptionId)
    {
        $subscription = DB::table('give_subscriptions')
            ->where('id', $subscriptionId)
            ->get();

        return SubscriptionQueryData::fromObject($subscription)->toSubscription();
    }

    /**
     * @unreleased
     *
     * @param  int  $donationId
     * @return Subscription
     */
    public function getByDonationId($donationId)
    {
        $subscription = DB::table('give_subscriptions')
            ->where('parent_payment_id', $donationId)
            ->get();

        return SubscriptionQueryData::fromObject($subscription)->toSubscription();
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @return array|Subscription[]
     */
    public function getByDonorId($donorId)
    {
        $subscriptions = DB::table('give_subscriptions')
            ->where('customer_id', $donorId)
            ->getAll();

        return array_map(static function ($object) {
            return SubscriptionQueryData::fromObject($object)->toSubscription();
        }, $subscriptions);
    }

    /**
     * @param int $id
     *
     * @return object[]
     */
    public function getNotesBySubscriptionId($id)
    {
       return DB::table('comments')
                ->select(
                    [ 'comment_content', 'note'],
                    ['comment_date', 'date' ]
                )
                ->where('comment_post_ID', $id)
                ->where('comment_type', 'give_sub_note')
                ->orderBy('comment_date', 'DESC')
                ->getAll();
    }

}
