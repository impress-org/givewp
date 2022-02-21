<?php

namespace Give\Donations\Repositories;

use Exception;
use Give\Donations\DataTransferObjects\DonationQueryData;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Log\Log;

/**
 * @unreleased
 */
class DonationRepository
{
    use InteractsWithTime;

    /**
     * @var string[]
     */
    private $requiredDonationProperties = [
        'status',
        'gateway',
        'amount',
        'currency',
        'donorId',
        'firstName',
        'lastName',
        'email',
    ];

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
                ['subscription_id', 'subscriptionId'],
                ['_give_payment_mode', 'mode']
            )
            ->where('ID', $donationId)
            ->get();

        if ( ! $donation) {
            return null;
        }

        return DonationQueryData::fromObject($donation)->toDonation();
    }

    /**
     * @unreleased
     *
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
     * @unreleased
     *
     * @param  int  $donorId
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
     * @unreleased
     *
     * @param  Donation  $donation
     *
     * @return Donation
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Donation $donation)
    {
        $this->validateDonation($donation);

        $date = $donation->createdAt ? $this->getFormattedDateTime(
            $donation->createdAt
        ) : $this->getCurrentFormattedDateForDatabase();


        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->insert([
                    'post_date' => $date,
                    'post_date_gmt' => get_gmt_from_date($date),
                    'post_status' => $donation->status->getValue(),
                    'post_type' => 'give_payment',
                    'post_parent' => isset($donation->parentId) ? $donation->parentId : 0
                ]);

            $donationId = DB::last_insert_id();

            foreach ($this->getCoreDonationMeta($donation) as $metaKey => $metaValue) {
                DB::table('give_donationmeta')
                    ->insert([
                        'donation_id' => $donationId,
                        'meta_key' => $metaKey,
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation');

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        return $this->getById($donationId);
    }

    /**
     * @unreleased
     *
     * @param  Donation  $donation
     *
     * @return Donation
     * @throws Exception|InvalidArgumentException
     */
    public function update(Donation $donation)
    {
        $this->validateDonation($donation);

        $date = $this->getCurrentFormattedDateForDatabase();

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->where('id', $donation->id)
                ->update([
                    'post_modified' => $date,
                    'post_modified_gmt' => get_gmt_from_date($date),
                    'post_status' => $donation->status->getValue(),
                    'post_type' => 'give_payment',
                    'post_parent' => isset($donation->parentId) ? $donation->parentId : 0
                ]);

            foreach ($this->getCoreDonationMeta($donation) as $metaKey => $metaValue) {
                DB::table('give_donationmeta')
                    ->where('donation_id', $donation->id)
                    ->where('meta_key', $metaKey)
                    ->update([
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation');

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        if ($donation->isDirty('status')) {
            /** @var DonationStatus $originalStatus */
            $originalStatus = $donation->getOriginal('status');

            $this->dispatchUpdatePaymentStatus(
                $donation->id,
                $donation->status->getValue(),
                $originalStatus->getValue()
            );
        }

        return $donation;
    }

    /**
     * @unreleased
     *
     * @param  Donation  $donation
     * @return bool
     * @throws Exception
     */
    public function delete(Donation $donation)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->where('id', $donation->id)
                ->delete();

            foreach ($this->getCoreDonationMeta($donation) as $metaKey => $metaValue) {
                DB::table('give_donationmeta')
                    ->where('donation_id', $donation->id)
                    ->where('meta_key', $metaKey)
                    ->delete();
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donation');

            throw new $exception('Failed deleting a donation');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @unreleased
     *
     * @param  Donation  $donation
     *
     * @return array
     */
    public function getCoreDonationMeta(Donation $donation)
    {
        $meta = [
            '_give_payment_total' => $donation->amount,
            '_give_payment_currency' => $donation->currency,
            '_give_payment_gateway' => $donation->gateway,
            '_give_payment_donor_id' => $donation->donorId,
            '_give_donor_billing_first_name' => $donation->firstName,
            '_give_donor_billing_last_name' => $donation->lastName,
            '_give_payment_donor_email' => $donation->email,
            '_give_payment_mode' => isset($donation->mode) ? $donation->mode : $this->getDefaultDonationMode()
        ];

        if (isset($donation->subscriptionId)) {
            $meta['subscription_id'] = $donation->subscriptionId;
        }

        return $meta;
    }

    /**
     *
     * @unreleased
     *
     * @param  int  $donationId
     *
     * @return int|null
     */
    public function getSequentialId($donationId)
    {
        $query = DB::table('give_sequential_ordering')->where('payment_id', $donationId)->get();

        if (!$query) {
            return null;
        }

        return (int)$query->id;
    }

    /**
     * @unreleased
     *
     * @param  int  $id
     *
     * @return object[]
     */
    public function getNotesByDonationId($id)
    {
        $notes = DB::table('give_comments')
            ->select(
                ['comment_content', 'note'],
                ['comment_date', 'date']
            )
            ->where('comment_parent', $id)
            ->where('comment_type', 'donation')
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
     * @param  Donation  $donation
     * @return void
     */
    private function validateDonation(Donation $donation)
    {
        foreach ($this->requiredDonationProperties as $key) {
            if (!isset($donation->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * Fires after changing donation status.
     *
     * @unreleased
     *
     * @param  int  $donationId
     * @param  string  $newStatus
     * @param  string  $originalStatus
     * @return void
     */
    private function dispatchUpdatePaymentStatus($donationId, $newStatus, $originalStatus)
    {
        do_action('give_update_payment_status', $donationId, $newStatus, $originalStatus);

        Log::notice('Donation Status Updated', compact('donationId', 'originalStatus', 'newStatus'));
    }

    /**
     * @return string
     */
    private function getDefaultDonationMode()
    {
        return give_is_test_mode() ? 'test' : 'live';
    }
}

