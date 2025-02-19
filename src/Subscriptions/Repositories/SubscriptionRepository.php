<?php

namespace Give\Subscriptions\Repositories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;

/**
 * @since 2.19.6
 */
class SubscriptionRepository
{
    /**
     * @var string[]
     */
    private $requiredSubscriptionProperties = [
        'donorId',
        'period',
        'frequency',
        'amount',
        'status',
        'donationFormId',
    ];

    /**
     * @since 2.19.6
     *
     * @return Subscription|null
     */
    public function getById(int $subscriptionId)
    {
        return $this->queryById($subscriptionId)->get();
    }

    /**
     * @since 2.27.0  Add support for multiple return types.
     * @since 2.21.0
     *
     * @return Subscription|null
     */
    public function getByGatewaySubscriptionId(string $gatewaySubscriptionId)
    {
        return $this->queryByGatewaySubscriptionId($gatewaySubscriptionId)->get();
    }

    /**
     * @since 2.19.6
     *
     * @param int $subscriptionId
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function queryById(int $subscriptionId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('id', $subscriptionId);
    }

    /**
     * @since 2.21.0
     *
     * @param string $gatewayTransactionId
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function queryByGatewaySubscriptionId(string $gatewayTransactionId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('profile_id', $gatewayTransactionId);
    }

    /**
     * @since 2.19.6
     *
     * @param int $donationId
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function queryByDonationId(int $donationId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('parent_payment_id', $donationId);
    }

    /**
     * @since 2.19.6
     *
     * @param int $donorId
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function queryByDonorId(int $donorId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('customer_id', $donorId);
    }

    /**
     * @since 2.19.6
     *
     * @return object[]
     */
    public function getNotesBySubscriptionId(int $id): array
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
     * @since 2.24.0 add payment_mode column to insert
     * @since 2.21.0 replace actions with givewp_subscription_creating and givewp_subscription_created
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function insert(Subscription $subscription)
    {
        $this->validateSubscription($subscription);

        if ($subscription->renewsAt === null) {
            $subscription->bumpRenewalDate();
        }

        Hooks::doAction('givewp_subscription_creating', $subscription);

        $dateCreated = Temporal::withoutMicroseconds($subscription->createdAt ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')->insert([
                'created' => Temporal::getFormattedDateTime($dateCreated),
                'expiration' => Temporal::getFormattedDateTime($subscription->renewsAt),
                'status' => $subscription->status->getValue(),
                'profile_id' => $subscription->gatewaySubscriptionId ?? '',
                'customer_id' => $subscription->donorId,
                'period' => $subscription->period->getValue(),
                'frequency' => $subscription->frequency,
                'initial_amount' => $subscription->amount->formatToDecimal(),
                'recurring_amount' => $subscription->amount->formatToDecimal(),
                'recurring_fee_amount' => $subscription->feeAmountRecovered !== null ? $subscription->feeAmountRecovered->formatToDecimal(
                ) : 0,
                'bill_times' => $subscription->installments,
                'transaction_id' => $subscription->transactionId ?? '',
                'product_id' => $subscription->donationFormId,
                'payment_mode' => $subscription->mode->getValue(),
            ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a subscription', compact('subscription'));

            throw new $exception('Failed creating a subscription');
        }

        DB::query('COMMIT');

        $subscriptionId = DB::last_insert_id();

        $subscription->id = $subscriptionId;
        $subscription->createdAt = $dateCreated;

        Hooks::doAction('givewp_subscription_created', $subscription);
    }

    /**
     * @since 3.17.0 add expiration column to update
     * @since 2.24.0 add payment_mode column to update
     * @since 2.21.0 replace actions with givewp_subscription_updating and givewp_subscription_updated
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function update(Subscription $subscription)
    {
        $this->validateSubscription($subscription);

        if ($subscription->renewsAt === null) {
            throw new InvalidArgumentException('renewsAt is required.');
        }

        Hooks::doAction('givewp_subscription_updating', $subscription);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'expiration' => Temporal::getFormattedDateTime($subscription->renewsAt),
                    'status' => $subscription->status->getValue(),
                    'profile_id' => $subscription->gatewaySubscriptionId,
                    'customer_id' => $subscription->donorId,
                    'period' => $subscription->period->getValue(),
                    'frequency' => $subscription->frequency,
                    'initial_amount' => $subscription->amount->formatToDecimal(),
                    'recurring_amount' => $subscription->amount->formatToDecimal(),
                    'recurring_fee_amount' => isset($subscription->feeAmountRecovered) ? $subscription->feeAmountRecovered->formatToDecimal(
                    ) : 0,
                    'bill_times' => $subscription->installments,
                    'transaction_id' => $subscription->transactionId ?? '',
                    'product_id' => $subscription->donationFormId,
                    'payment_mode' => $subscription->mode->getValue(),
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a subscription', compact('subscription'));

            throw new $exception('Failed updating a subscription');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_subscription_updated', $subscription);
    }

    /**
     * @since 2.21.0 replace actions with givewp_subscription_deleting and givewp_subscription_deleted
     * @since 2.20.0 consolidate meta deletion into a single query
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete(Subscription $subscription): bool
    {
        Hooks::doAction('givewp_subscription_deleting', $subscription);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')
                ->where('id', $subscription->id)
                ->delete();

            DB::table('give_subscriptionmeta')
                ->where('subscription_id', $subscription->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a subscription', compact('subscription'));

            throw new $exception('Failed deleting a subscription');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_subscription_deleted', $subscription);

        return true;
    }

    /**
     * Up to this point the donation is created first and then the subscription, and the donation is stored as the
     * parent_payment_id of the subscription. This is backwards and should not be the case. But legacy code depends on
     * this value, so we still need to store it for now.
     *
     * This should only be used when creating a new Subscription with its corresponding Donation. Do not add this value
     * to the Subscription model as it should not be reference moving forward.
     *
     * @since 2.24.0 Save payment mode to subscription meta
     * @since 2.23.0
     *
     * @return void
     */
    public function updateLegacyParentPaymentId(int $subscriptionId, int $donationId)
    {
        $mode = give_get_meta($donationId, DonationMetaKeys::MODE, true) ?? (give_is_test_mode() ? 'test' : 'live');

        DB::table('give_subscriptions')
            ->where('id', $subscriptionId)
            ->update([
                'parent_payment_id' => $donationId,
                'payment_mode' => $mode,
            ]);
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function updateLegacyColumns(int $subscriptionId, array $columns): bool
    {
        foreach (Subscription::propertyKeys() as $key) {
            if (array_key_exists($key, $columns)) {
                throw new InvalidArgumentException("'$key' is not a legacy column.");
            }
        }

        DB::query('START TRANSACTION');

        try {
            DB::table('give_subscriptions')
                ->where('id', $subscriptionId)
                ->update($columns);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a subscription', compact('subscriptionId', 'columns'));

            throw new $exception('Failed updating a subscription');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * Sets the payment mode for a given subscription
     *
     * @since 2.24.0
     *
     * @return void
     */
    public function updatePaymentMode(int $subscriptionId, SubscriptionMode $mode)
    {
        DB::table('give_subscriptions')
            ->where('id', $subscriptionId)
            ->update([
                'payment_mode' => $mode->getValue(),
            ]);
    }

    /**
     * @since 2.23.0 update to no longer rely on parent_payment_id column as it will be deprecated
     * @since 2.19.6
     *
     * @return int|null
     */
    public function getInitialDonationId(int $subscriptionId)
    {
        $query = DB::table('posts')
            ->select('ID')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                [DonationMetaKeys::SUBSCRIPTION_ID, 'subscriptionId'],
                [DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION, 'initialDonationId']
            )
            ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', $subscriptionId)
            ->where('give_donationmeta_attach_meta_initialDonationId.meta_value', 1)
            ->get();

        if (!$query) {
            return null;
        }

        return (int)$query->ID;
    }

    /**
     * @since 3.20.0
     * @throws Exception
     */
   public function createRenewal(Subscription $subscription, array $attributes = []): Donation
   {
       $initialDonation = $subscription->initialDonation();

        $donation = Donation::create(
            array_merge([
                'subscriptionId' => $subscription->id,
                'gatewayId' => $subscription->gatewayId,
                'amount' => $subscription->amount,
                'status' => DonationStatus::COMPLETE(),
                'type' => DonationType::RENEWAL(),
                'donorId' => $subscription->donorId,
                'formId' => $subscription->donationFormId,
                'honorific' => $initialDonation->honorific,
                'firstName' => $initialDonation->firstName,
                'lastName' => $initialDonation->lastName,
                'email' => $initialDonation->email,
                'phone' => $initialDonation->phone,
                'anonymous' => $initialDonation->anonymous,
                'levelId' => $initialDonation->levelId,
                'company' => $initialDonation->company,
                'comment' => $initialDonation->comment,
                'billingAddress' => $initialDonation->billingAddress,
                'feeAmountRecovered' => $subscription->feeAmountRecovered,
                'exchangeRate' => $initialDonation->exchangeRate,
                'formTitle' => $initialDonation->formTitle,
                'mode' => $subscription->mode->isLive() ? DonationMode::LIVE() : DonationMode::TEST(),
                'donorIp' => $initialDonation->donorIp,
            ], $attributes)
        );

        $subscription->bumpRenewalDate();
        $subscription->save();

        return $donation;
    }

    /**
     * @since 2.19.6
     *
     * @return void
     */
    private function validateSubscription(Subscription $subscription)
    {
        foreach ($this->requiredSubscriptionProperties as $key) {
            if (!isset($subscription->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }

        if (!$subscription->donor) {
            throw new InvalidArgumentException("Invalid donorId, Donor does not exist");
        }
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Subscription::class);

        return $builder->from('give_subscriptions')
            ->select(
                'id',
                ['created', 'createdAt'],
                ['expiration', 'renewsAt'],
                ['customer_id', 'donorId'],
                'period',
                ['frequency', 'frequency'],
                ['bill_times', 'installments'],
                ['transaction_id', 'transactionId'],
                ['payment_mode', 'mode'],
                ['recurring_amount', 'amount'],
                ['recurring_fee_amount', 'feeAmount'],
                'status',
                ['profile_id', 'gatewaySubscriptionId'],
                ['product_id', 'donationFormId']
            )
            ->attachMeta(
                'give_donationmeta',
                'parent_payment_id',
                'donation_id',
                [DonationMetaKeys::GATEWAY, 'gatewayId'],
                [DonationMetaKeys::CURRENCY, 'currency']
            );
    }
}
