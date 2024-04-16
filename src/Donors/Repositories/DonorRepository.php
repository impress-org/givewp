<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Exceptions\FailedDonorUpdateException;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorModelQueryBuilder;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Donors\ValueObjects\DonorType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @since 2.19.6
 */
class DonorRepository
{

    /**
     * @var string[]
     */
    private $requiredDonorProperties = [
        'name',
        'firstName',
        'lastName',
        'email',
    ];

    /**
     * Query Donor By ID
     *
     * @since 2.24.0 replace ModelQueryBuilder with DonorModelQueryBuilder
     * @since 2.19.6
     *
     * @return DonorModelQueryBuilder<Donor>
     */
    public function queryById(int $donorId): DonorModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('id', $donorId);
    }

    /**
     * Get Donor By ID
     *
     * @since 2.19.6
     *
     * @return Donor|null
     */
    public function getById(int $donorId)
    {
        return $this->queryById($donorId)->get();
    }

    /**
     * Get Donor By WP User ID
     *
     * @since 2.19.6
     *
     * @return Donor|null
     */
    public function getByWpUserId(int $userId)
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
     * @since 2.19.6
     *
     * @return array|bool
     */
    public function getAdditionalEmails(int $donorId)
    {
        $additionalEmails = DB::table('give_donormeta')
            ->select(['meta_value', 'email'])
            ->where('meta_key', DonorMetaKeys::ADDITIONAL_EMAILS)
            ->where('donor_id', $donorId)
            ->getAll();

        if (!$additionalEmails) {
            return null;
        }

        return array_column($additionalEmails, 'email');
    }

    /**
     * @since 3.7.0 Add support to "phone" property
     * @since 2.24.0 add support for $donor->totalAmountDonated and $donor->totalNumberOfDonation
     * @since 2.21.0 add actions givewp_donor_creating and givewp_donor_created
     * @since 2.20.0 mutate model and return void
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function insert(Donor $donor)
    {
        $this->validateDonor($donor);

        Hooks::doAction('givewp_donor_creating', $donor);

        $dateCreated = Temporal::withoutMicroseconds($donor->createdAt ?: Temporal::getCurrentDateTime());

        DB::query('START TRANSACTION');

        $args = [
            'date_created' => Temporal::getFormattedDateTime($dateCreated),
            'user_id' => $donor->userId ?? 0,
            'email' => $donor->email,
            'name' => $donor->name,
        ];

        if (isset($donor->phone)) {
            $args['phone'] = $donor->phone;
        }

        if (isset($donor->totalAmountDonated)) {
            $args['purchase_value'] = $donor->totalAmountDonated->formatToDecimal();
        }

        if (isset($donor->totalNumberOfDonations)) {
            $args['purchase_count'] = $donor->totalNumberOfDonations;
        }

        try {
            DB::table('give_donors')
                ->insert($args);

            $donorId = DB::last_insert_id();

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                DB::table('give_donormeta')
                    ->insert([
                        'donor_id' => $donorId,
                        'meta_key' => $metaKey,
                        'meta_value' => $metaValue,
                    ]);
            }

            if (isset($donor->additionalEmails)) {
                foreach ($donor->additionalEmails as $additionalEmail) {
                    DB::table('give_donormeta')
                        ->insert([
                            'donor_id' => $donorId,
                            'meta_key' => DonorMetaKeys::ADDITIONAL_EMAILS,
                            'meta_value' => $additionalEmail,
                        ]);
                }
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donor', compact('donor'));

            throw new $exception('Failed creating a donor');
        }

        DB::query('COMMIT');

        $donor->id = $donorId;
        $donor->createdAt = $dateCreated;

        Hooks::doAction('givewp_donor_created', $donor);
    }

    /**
     * @since 3.7.0 Add support to "phone" property
     * @since 2.24.0 add support for $donor->totalAmountDonated and $donor->totalNumberOfDonation
     * @since 2.23.1 use give()->donor_meta to update meta so data is upserted
     * @since 2.21.0 add actions givewp_donor_updating and givewp_donor_updated
     * @since 2.20.0 return void
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function update(Donor $donor)
    {
        $this->validateDonor($donor);

        Hooks::doAction('givewp_donor_updating', $donor);

        DB::query('START TRANSACTION');

        $args = [
            'user_id' => $donor->userId,
            'email' => $donor->email,
            'phone' => $donor->phone,
            'name' => $donor->name
        ];

        if (isset($donor->totalAmountDonated) && $donor->isDirty('totalAmountDonated')) {
            $args['purchase_value'] = $donor->totalAmountDonated->formatToDecimal();
        }

        if (isset($donor->totalNumberOfDonations) && $donor->isDirty('totalNumberOfDonations')) {
            $args['purchase_count'] = $donor->totalNumberOfDonations;
        }

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->update($args);

            foreach ($this->getCoreDonorMeta($donor) as $metaKey => $metaValue) {
                give()->donor_meta->update_meta($donor->id, $metaKey, $metaValue);
            }

            if (isset($donor->additionalEmails) && $donor->isDirty('additionalEmails')) {
                $this->updateAdditionalEmails($donor);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donor', compact('donor'));

            throw new FailedDonorUpdateException($donor, 0, $exception);
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_donor_updated', $donor);
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function updateLegacyColumns(int $donorId, array $columns): bool
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
     *
     * @since 2.21.0 add actions givewp_donor_deleting and givewp_donor_deleted
     * @since 2.20.0 consolidate meta deletion into a single query
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete(Donor $donor): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_donor_deleting', $donor);

        try {
            DB::table('give_donors')
                ->where('id', $donor->id)
                ->delete();

            DB::table('give_donormeta')
                ->where('donor_id', $donor->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donor', compact('donor'));

            throw new $exception('Failed deleting a donor');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_donor_deleted', $donor);

        return true;
    }

    /**
     * @since 2.19.6
     */
    private function getCoreDonorMeta(Donor $donor): array
    {
        return [
            DonorMetaKeys::FIRST_NAME => $donor->firstName,
            DonorMetaKeys::LAST_NAME => $donor->lastName,
            DonorMetaKeys::PREFIX => $donor->prefix ?? null,
        ];
    }

    /**
     * @since 2.19.6
     *
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
     * @since 2.21.1 optimize query by skipping prepareQuery until found
     * @since 2.19.6
     *
     * @return Donor|null
     */
    public function getByEmail(string $email)
    {
        $queryByPrimaryEmail = DB::table('give_donors')
            ->select(
                'id',
                'email'
            )
            ->where('email', $email)
            ->get();

        if ($queryByPrimaryEmail) {
            return $this->queryById($queryByPrimaryEmail->id)->get();
        }

        return $this->getByAdditionalEmail($email);
    }

    /**
     * @since 2.19.6
     *
     * @return Donor|null
     */
    public function getByAdditionalEmail(string $email)
    {
        $donorMetaObject = DB::table('give_donormeta')
            ->select(['donor_id', 'id'])
            ->where('meta_key', DonorMetaKeys::ADDITIONAL_EMAILS)
            ->where('meta_value', $email)
            ->get();

        if (!$donorMetaObject) {
            return null;
        }

        return $this->getById($donorMetaObject->id);
    }

    /**
     * @since 3.7.0 Add support to "phone" property
     * @since 2.24.0 replace ModelQueryBuilder with DonorModelQueryBuilder
     * @since 2.19.6
     *
     * @return DonorModelQueryBuilder<Donor>
     */
    public function prepareQuery(): DonorModelQueryBuilder
    {
        $builder = new DonorModelQueryBuilder(Donor::class);

        return $builder->from('give_donors')
            ->select(
                'id',
                ['user_id', 'userId'],
                'email',
                'phone',
                'name',
                ['purchase_value', 'totalAmountDonated'],
                ['purchase_count', 'totalNumberOfDonations'],
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
                ...DonorMetaKeys::getColumnsForAttachMetaQueryWithoutAdditionalEmails()
            );
    }

    /**
     * Additional emails are assigned to the same additional_email meta key.
     * In order to update them we need to delete and re-insert.
     *
     * @since 2.19.6
     *
     * @return void
     */
    private function updateAdditionalEmails(Donor $donor)
    {
        foreach ($donor->additionalEmails as $additionalEmail) {
            DB::table('give_donormeta')
                ->where('donor_id', $donor->id)
                ->where('meta_key', DonorMetaKeys::ADDITIONAL_EMAILS)
                ->where('meta_value', $additionalEmail)
                ->delete();
        }

        foreach ($donor->additionalEmails as $additionalEmail) {
            DB::table('give_donormeta')
                ->where('donor_id', $donor->id)
                ->insert([
                    'donor_id' => $donor->id,
                    'meta_key' => DonorMetaKeys::ADDITIONAL_EMAILS,
                    'meta_value' => $additionalEmail,
                ]);
        }
    }

    /**
     * @since 2.20.0
     *
     * @return string|null
     */
    public function getDonorLatestDonationDate(int $donorId)
    {
        $donation = DB::table('posts')
            ->select('post_date')
            ->leftJoin('give_donationmeta', 'ID', 'donation_id')
            ->where('post_type', 'give_payment')
            ->where('meta_key', DonationMetaKeys::DONOR_ID)
            ->where('meta_value', $donorId)
            ->orderBy('ID', 'DESC')
            ->limit(1)
            ->get();

        if ($donation) {
            return $donation->post_date;
        }

        return null;
    }

    /**
     * @since 2.24.0 change return to DonorType
     * @since 2.20.0
     *
     * @return DonorType|null
     */
    public function getDonorType(int $donorId)
    {
        $donor = DB::table('give_donors')
            ->select(
                'id',
                ['purchase_count', 'donationCount'],
                ['payment_ids', 'paymentIds']
            )
            ->where('id', $donorId)
            ->get();

        if (!$donor) {
            return null;
        }

        if (!$donor->donationCount) {
            return DonorType::NEW();
        }

        // Donation IDs
        $ids = strpos($donor->paymentIds, ',')
            ? explode(',', $donor->paymentIds)
            : [$donor->paymentIds];

        // Recurring
        $recurringDonations = DB::table('posts')
            ->leftJoin('give_donationmeta', 'id', 'donation_id')
            ->whereIn('donation_id', $ids)
            ->where('meta_key', DonationMetaKeys::IS_RECURRING)
            ->where('meta_value', '1')
            ->count();

        if ($recurringDonations) {
            return DonorType::SUBSCRIBER();
        }

        if ((int)$donor->donationCount > 1) {
            return DonorType::REPEAT();
        }

        return DonorType::SINGLE();
    }

    /**
     * @since 2.20.0
     */
    public function getDonorsCount(): int
    {
        return DB::table('give_donors')->count();
    }
}
