<?php

namespace Give\Donors\Repositories;

use Exception;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Log\Log;
use WP_REST_Request;

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
     * @since 2.19.6
     *
     * @param int $donorId
     * @return ModelQueryBuilder
     */
    public function queryById($donorId)
    {
        return $this->prepareQuery()
            ->where('id', $donorId);
    }

    /**
     * Get Donor By ID
     *
     * @since 2.19.6
     *
     * @param int $donorId
     * @return Donor|null
     */
    public function getById($donorId)
    {
        return $this->queryById($donorId)->get();
    }

    /**
     * Get Donor By WP User ID
     *
     * @since 2.19.6
     *
     * @param int $userId
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
     * @since 2.19.6
     *
     * @param int $donorId
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
     * @unreleased mutate model and return void
     * @since 2.19.6
     *
     * @param Donor $donor
     *
     * @return void
     * @throws Exception
     */
    public function insert(Donor $donor)
    {
        $this->validateDonor($donor);

        $dateCreated = $donor->createdAt ?: Temporal::getCurrentDateTime();

        DB::query('START TRANSACTION');

        try {
            DB::table('give_donors')
                ->insert([
                    'date_created' => Temporal::getFormattedDateTime($dateCreated),
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
    }

    /**
     * @unreleased return void
     * @since 2.19.6
     *
     * @param Donor $donor
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

            if (isset($donor->additionalEmails) && $donor->isDirty('additionalEmails')) {
                $this->updateAdditionalEmails($donor);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donor', compact('donor'));

            throw new $exception('Failed updating a donor');
        }

        DB::query('COMMIT');
    }

    /**
     * @since 2.19.6
     *
     * @param int $donorId
     * @param array $columns
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
     * @unreleased consolidate meta deletion into a single query
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete(Donor $donor)
    {
        DB::query('START TRANSACTION');

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

        return true;
    }

    /**
     * @since 2.19.6
     *
     * @param Donor $donor
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
     * @since 2.19.6
     *
     * @param Donor $donor
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
     * @param string $email
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
     * @param string $email
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
     * @return ModelQueryBuilder<Donor>
     */
    public function prepareQuery()
    {
        $builder = new ModelQueryBuilder(Donor::class);

        return $builder->from('give_donors')
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
                ...DonorMetaKeys::getColumnsForAttachMetaQueryWithAdditionalEmails()
            );
    }

    /**
     * Additional emails are assigned to the same additional_email meta key.
     * In order to update them we need to delete and re-insert.
     *
     * @since 2.19.6
     *
     * @param Donor $donor
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
     * @unreleased
     * @param int $donorId
     * @return string|null
     */
    public function getDonorLatestDonationDate($donorId)
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
     * @unreleased
     * @param int $donorId
     * @return string|null
     */
    public function getDonorType($donorId)
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
            return 'new';
        }

        // Donation IDs
        $ids = strpos($donor->paymentIds, ',')
            ? explode(',', $donor->paymentIds)
            : [$donor->paymentIds];

        // Recurring
        $recurringDonations = DB::table('posts')
            ->leftJoin('give_donationmeta', 'id', 'donation_id')
            ->whereIn('donation_id', $ids)
            ->where( 'meta_key', DonationMetaKeys::IS_RECURRING)
            ->where( 'meta_value', '1')
            ->count();

        if ($recurringDonations) {
            return 'subscriber';
        }

        if ((int)$donor->donationCount > 1 ) {
            return 'repeat';
        }

        return 'single';
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return array
     */
    public function getDonorsForRequest(WP_REST_Request $request)
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('perPage');

        $query = DB::table('give_donors')
            ->select(
                'id',
                ['user_id', 'userId'],
                'email',
                'name',
                ['purchase_value', 'donationRevenue'],
                ['purchase_count', 'donationCount'],
                ['payment_ids', 'paymentIds'],
                ['date_created', 'createdAt']
            )
            ->attachMeta(
                'give_donormeta',
                'id',
                'donor_id',
                ['_give_donor_title_prefix', 'titlePrefix']
            )
            ->limit($perPage)
            ->orderBy('id', 'DESC')
            ->offset(($page - 1) * $perPage);

        $query = $this->getWhereConditionsForRequest($query, $request);

        $query->limit($perPage);

        return $query->getAll();
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return int
     */
    public function getTotalDonorsCountForRequest(WP_REST_Request $request)
    {
        $query = DB::table('give_donors');
        $query = $this->getWhereConditionsForRequest($query, $request);

        return $query->count();
    }

    /**
     * @unreleased
     * @return int
     */
    public function getDonorsCount()
    {
        return DB::table('give_donors')->count();
    }

    /**
     * @param QueryBuilder $builder
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return QueryBuilder
     */
    private function getWhereConditionsForRequest(QueryBuilder $builder, WP_REST_Request $request)
    {
        $search = $request->get_param('search');
        $start = $request->get_param('start');
        $end = $request->get_param('end');
        $form = $request->get_param('form');

        if ($search) {
            if (ctype_digit($search)) {
                $builder->where('id', $search);
            } else {
                $builder->whereLike('name', $search);
                $builder->orWhereLike('email', $search);
            }
        }

        if ($start && $end) {
            $builder->whereBetween('date_created', $start, $end);
        } else if ($start) {
            $builder->where('date_created', $start, '>=');
        } else if ($end) {
            $builder->where('date_created', $end, '<=');
        }

        if ($form) {
            $builder
                ->whereIn('id', static function (QueryBuilder $builder) use ($form) {
                    $builder
                        ->from('give_donationmeta')
                        ->distinct()
                        ->select('meta_value')
                        ->where('meta_key', '_give_payment_donor_id')
                        ->whereIn('donation_id', static function (QueryBuilder $builder) use ($form) {
                            $builder
                                ->from('give_donationmeta')
                                ->select('donation_id')
                                ->where('meta_key', '_give_payment_form_id')
                                ->where('meta_value', $form);
                        });
                });
        }

        return $builder;
    }
}
