<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Log\Log;

class DonorRepository
{

    /**
     * @var string[]
     */
    private $requiredDonorProperties = [
        // TODO: name should be an accessor
        'name',
        'firstName',
        'lastName',
        'email',
    ];

    /**
     * Query Donor By ID
     *
     * @unreleased
     *
     * @param  int  $donorId
     * @return QueryBuilder
     */
    public function queryById($donorId)
    {
        return $this->prepareQuery()
            ->where('id', $donorId);
    }

    /**
     * Get Donor By ID
     *
     * @unreleased
     *
     * @param  int  $donorId
     * @return Donor|null
     */
    public function getById($donorId)
    {
        return $this->queryById($donorId)->get();
    }

    /**
     * Get Donor By WP User ID
     *
     * @unreleased
     *
     * @param  int  $userId
     * @return Donor|null
     */
    public function getByWpUserId($userId)
    {
        // user_id can technically be 0 so make sure to return null
        if (!$userId) {
            return null;
        }

        return $this->prepareQuery()
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @return array|bool
     */
    public function getAdditionalEmails($donorId)
    {
        $additionalEmails = DB::table('give_donormeta')
            ->select(['meta_value', 'email'])
            ->where('meta_key', 'additional_email')
            ->where('donor_id', $donorId)
            ->getAll();

        if (!$additionalEmails) {
            return null;
        }

        return array_column($additionalEmails, 'email');
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     *
     * @return Donor
     * @throws Exception
     */
    public function insert(Donor $donor)
    {
        $this->validateDonor($donor);

        $date = $donor->createdAt ? Temporal::getFormattedDateTime(
            $donor->createdAt
        ) : Temporal::getCurrentFormattedDateForDatabase();

        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->insert([
                    'date_created' => $date,
                    'user_id' => isset($donor->userId) ? $donor->userId : 0,
                    'email' => $donor->email,
                    'name' => $donor->name
                ]);

            $donorId = DB::last_insert_id();

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->insert([
                        'donor_id' => $donorId,
                        'meta_key' => $metaKey,
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donor', compact('donor'));

            throw new $exception('Failed creating a donor');
        }

        DB::query('COMMIT');

        return $this->getById($donorId);
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return Donor
     * @throws Exception
     */
    public function update(Donor $donor)
    {
        $this->validateDonor($donor);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->update([
                    'user_id' => $donor->userId,
                    'email' => $donor->email,
                    'name' => $donor->name
                ]);

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->where('donor_id', $donor->id)
                    ->where('meta_key', $metaKey)
                    ->update([
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donor', compact('donor'));

            throw new $exception('Failed updating a donor');
        }

        DB::query('COMMIT');

        return $donor;
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @param  array  $columns
     * @return bool
     * @throws Exception
     */
    public function updateLegacyColumns($donorId, $columns)
    {
        DB::query('START TRANSACTION');

        foreach (Donor::propertyKeys() as $key) {
            if (array_key_exists($key, $columns)) {
                throw new InvalidArgumentException("'$key' is not a legacy column.");
            }
        }

        try {
            DB::table('give_donors')
                ->where('id', $donorId)
                ->update($columns);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donor', compact('donorId', 'columns'));

            throw new $exception('Failed updating a donor');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @throws Exception
     */
    public function delete(Donor $donor)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->delete();

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->where('donor_id', $donor->id)
                    ->where('meta_key', $metaKey)
                    ->delete();
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donor', compact('donor'));

            throw new $exception('Failed deleting a donor');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return array
     */
    private function getCoreDonorMeta(Donor $donor)
    {
        return [
            DonorMetaKeys::FIRST_NAME => $donor->firstName,
            DonorMetaKeys::LAST_NAME => $donor->lastName,
            DonorMetaKeys::PREFIX => isset($donor->prefix) ? $donor->prefix : null,
        ];
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     * @return void
     */
    private function validateDonor(Donor $donor)
    {
        foreach ($this->requiredDonorProperties as $key) {
            if (!isset($donor->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @param  string  $email
     * @return Donor
     */
    public function getByEmail($email)
    {
        $donorObjectByPrimaryEmail = $this->prepareQuery()
            ->where('email', $email)
            ->get();

        if (!$donorObjectByPrimaryEmail) {
            return $this->getByAdditionalEmail($email);
        }

        return $donorObjectByPrimaryEmail;
    }

    /**
     * @param  string  $email
     * @return Donor
     */
    public function getByAdditionalEmail($email)
    {
        $donorMetaObject = DB::table('give_donormeta')
            ->select(['donor_id', 'id'])
            ->where('meta_key', 'additional_email')
            ->where('meta_value', $email)
            ->get();

        if (!$donorMetaObject) {
            return null;
        }

        return $this->getById($donorMetaObject->id);
    }

    /**
     * @unreleased
     *
     * @param  int  $donorId
     * @param  string  $additionalEmail
     * @return bool
     * @throws Exception
     */
    public function insertAdditionalEmail($donorId, $additionalEmail)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('give_donormeta')
                ->insert([
                    'donor_id' => $donorId,
                    'meta_key' => 'additional_email',
                    'meta_value' => $additionalEmail,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed adding additional donor email', compact('donorId', 'additionalEmail'));

            throw new $exception('Failed adding additional donor email');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     * @return QueryBuilder
     */
    public function prepareQuery()
    {
        return DB::table('give_donors')
            ->setModel(Donor::class)
            ->select(
                'id',
                ['user_id', 'userId'],
                'email',
                'name',
                ['purchase_value', 'totalAmountDonated'],
                ['purchase_count', 'totalDonations'],
                ['payment_ids', 'paymentIds'],
                ['date_created', 'createdAt'],
                'token',
                ['verify_key', 'verifyKey'],
                ['verify_throttle', 'verifyThrottle']
            )
            ->attachMeta(
                'give_donormeta',
                'ID',
                'donor_id',
                ...DonorMetaKeys::getColumnsForAttachMetaQuery()
            );
    }
}
