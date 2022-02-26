<?php

namespace Give\Subscriptions\Repositories;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Log\Log;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionRepository
{
    use InteractsWithTime;

    /**
     * @var string[]
     */
    private $requiredSubscriptionProperties = [
        'donorId',
        'period',
        'frequency',
        'amount',
        'status',
        'donationFormId'
    ];

    /**
     * @unreleased
     *
     * @param  int  $subscriptionId
     * @return Subscription
     */
    public function getById($subscriptionId)
    {
        return $this->prepareQuery()
            ->where('id', $subscriptionId)
            ->get();
    }

    /**
     * @unreleased
     *
     * @param  int  $donationId
     * @return Subscription
     */
    public function getByDonationId($donationId)
    {
        return $this->prepareQuery()
            ->where('parent_payment_id', $donationId)
            ->get();
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @return Subscription
     */
    public function getByDonorId($donorId)
    {
        return $this->prepareQuery()
            ->where('customer_id', $donorId)
            ->getAll();
    }

    /**
     * @unreleased
     *
     * @param  int  $id
     *
     * @return object[]
     */
    public function getNotesBySubscriptionId($id)
    {
        $notes = DB::table('comments')
            ->select(
                ['comment_content', 'note'],
                ['comment_date', 'date']
            )
            ->where('comment_post_ID', $id)
            ->where('comment_type', 'give_sub_note')
            ->orderBy('comment_date', 'DESC')
            ->getAll();

        if (!$notes) {
            return [];
        }

        return $notes;
    }

    /**
     * @unreleased
     *
     * @param  Subscription  $subscription
     *
     * @return Subscription
     * @throws Exception
     */
    public function insert(Subscription $subscription)
    {
        $this->validateSubscription($subscription);

        $date = $subscription->createdAt ? $this->getFormattedDateTime(
            $subscription->createdAt
        ) : $this->getCurrentFormattedDateForDatabase();

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')->insert([
                'created' => $date,
                'status' => $subscription->status->getValue(),
                'profile_id' => $subscription->gatewaySubscriptionId,
                'customer_id' => $subscription->donorId,
                'period' => $subscription->period->getValue(),
                'frequency' => $subscription->frequency,
                'initial_amount' => $subscription->amount,
                'recurring_amount' => $subscription->amount,
                'recurring_fee_amount' => isset($subscription->feeAmount) ? $subscription->feeAmount : 0,
                'bill_times' => isset($subscription->installments) ? $subscription->installments : 0,
                'transaction_id' => $subscription->transactionId,
                'product_id' => $subscription->donationFormId
            ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a subscription');

            throw new $exception('Failed creating a subscription');
        }

        DB::query('COMMIT');

        $subscriptionId = DB::last_insert_id();

        return $this->getById($subscriptionId);
    }

    /**
     * @unreleased
     *
     * @param  Subscription  $subscription
     *
     * @return Subscription
     * @throws Exception
     */
    public function update(Subscription $subscription)
    {
        $this->validateSubscription($subscription);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'status' => $subscription->status->getValue(),
                    'profile_id' => $subscription->gatewaySubscriptionId,
                    'customer_id' => $subscription->donorId,
                    'period' => $subscription->period->getValue(),
                    'frequency' => $subscription->frequency,
                    'initial_amount' => $subscription->amount,
                    'recurring_amount' => $subscription->amount,
                    'recurring_fee_amount' => isset($subscription->feeAmount) ? $subscription->feeAmount : 0,
                    'bill_times' => isset($subscription->installments) ? $subscription->installments : 0,
                    'transaction_id' => $subscription->transactionId,
                    'product_id' => $subscription->donationFormId
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a subscription');

            throw new $exception('Failed updating a subscription');
        }

        DB::query('COMMIT');

        $subscriptionId = DB::last_insert_id();

        return $this->getById($subscriptionId);
    }

    /**
     * @unreleased
     *
     * @param  Subscription  $subscription
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(Subscription $subscription)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')
                ->where('id', $subscription->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a subscription');

            throw new $exception('Failed deleting a subscription');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @unreleased
     *
     * @param  Subscription  $subscription
     * @return void
     */
    private function validateSubscription(Subscription $subscription)
    {
        foreach ($this->requiredSubscriptionProperties as $key) {
            if (!isset($subscription->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @unreleased
     *
     * @return QueryBuilder
     */
    public function prepareQuery()
    {
        return DB::table('give_subscriptions')
            ->setModel(Subscription::class)
            ->select(
                ['id', 'id'],
                ['created', 'createdAt'],
                ['expiration', 'expiresAt'],
                ['customer_id', 'donorId'],
                ['period', 'period'],
                ['frequency', 'frequency'],
                ['bill_times', 'installments'],
                ['transaction_id', 'transactionId'],
                ['recurring_amount', 'amount'],
                ['recurring_fee_amount', 'feeAmount'],
                ['status', 'status'],
                ['profile_id', 'gatewaySubscriptionId'],
                ['product_id', 'donationFormId']
            );
    }

}
