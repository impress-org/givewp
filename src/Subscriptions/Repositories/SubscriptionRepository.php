<?php

namespace Give\Subscriptions\Repositories;

use Give\Framework\Database\DB;
use Give\Subscriptions\DataTransferObjects\SubscriptionQueryData;
use Give\Subscriptions\Models\Subscription;

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
        $query = DB::table(DB::prefix('give_subscriptions'))
            ->select('*')
            ->where('id', $subscriptionId)
            ->get();

        return SubscriptionQueryData::fromObject($query)->toSubscription();
    }

    /**
     * @unreleased
     *
     * @param  int  $donationId
     * @return Subscription
     */
    public function getByDonationId($donationId)
    {
        $query = DB::table(DB::prefix('give_subscriptions'))
            ->select('*')
            ->where('parent_payment_id', $donationId)
            ->get();

        return SubscriptionQueryData::fromObject($query)->toSubscription();
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @return array|Subscription[]
     */
    public function getByDonorId($donorId)
    {
        $query = DB::table(DB::prefix('give_subscriptions'))
            ->select('*')
            ->where('customer_id', $donorId)
            ->getAll();

        return array_map(static function ($object) {
            return SubscriptionQueryData::fromObject($object)->toSubscription();
        }, $query);
    }

}
