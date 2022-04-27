<?php

namespace Give\Donations\Repositories;

use Exception;
use Give\Donations\Actions\GeneratePurchaseKey;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Call;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\ValueObjects\Money;
use WP_REST_Request;

/**
 * @since 2.19.6
 */
class DonationRepository
{

    /**
     * @since 2.19.6
     *
     * @var string[]
     */
    private $requiredDonationProperties = [
        'formId',
        'status',
        'gateway',
        'amount',
        'currency',
        'donorId',
        'firstName',
        'email',
    ];

    /**
     * Get Donation By ID
     *
     * @since 2.19.6
     *
     * @param int $donationId
     *
     * @return Donation|null
     */
    public function getById($donationId)
    {
        return $this->prepareQuery()
            ->where('ID', $donationId)
            ->get();
    }

    /**
     * @since 2.19.6
     *
     * @param int $subscriptionId
     *
     * @return Donation[]|null
     */
    public function getBySubscriptionId($subscriptionId)
    {
        return $this->queryBySubscriptionId($subscriptionId)->getAll();
    }

    /**
     * @since 2.19.6
     *
     * @param int $subscriptionId
     *
     * @return ModelQueryBuilder
     */
    public function queryBySubscriptionId($subscriptionId)
    {
        $initialDonationId = give()->subscriptions->getInitialDonationId($subscriptionId);

        $renewals = $this->prepareQuery()
            ->where('post_type', 'give_payment')
            ->where('post_status', 'give_subscription')
            ->whereIn('ID', function (QueryBuilder $builder) use ($subscriptionId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', DonationMetaKeys::SUBSCRIPTION_ID)
                    ->where('meta_value', $subscriptionId);
            });

        return $renewals->orWhere('ID', $initialDonationId)->orderBy('post_date', 'DESC');
    }

    /**
     * @since 2.19.6
     *
     * @param int $donorId
     *
     * @return ModelQueryBuilder
     */
    public function queryByDonorId($donorId)
    {
        return $this->prepareQuery()
            ->where('post_type', 'give_payment')
            ->whereIn('ID', function (QueryBuilder $builder) use ($donorId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', DonationMetaKeys::DONOR_ID)
                    ->where('meta_value', $donorId);
            })
            ->orderBy('post_date', 'DESC');
    }

    /**
     * @unreleased mutate model and return void
     * @since 2.19.6
     *
     * @param Donation $donation
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Donation $donation)
    {
        $this->validateDonation($donation);

        Hooks::doAction('give_donation_creating', $donation);

        $dateCreated = $donation->createdAt ?: Temporal::getCurrentDateTime();
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->insert([
                    'post_date' => $dateCreatedFormatted,
                    'post_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'post_modified' => $dateCreatedFormatted,
                    'post_modified_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'post_status' => $donation->status->getValue(),
                    'post_type' => 'give_payment',
                    'post_parent' => isset($donation->parentId) ? $donation->parentId : 0
                ]);

            $donationId = DB::last_insert_id();

            $donationMeta = $this->getCoreDonationMetaForDatabase($donation);

            foreach ($donationMeta as $metaKey => $metaValue) {
                DB::table('give_donationmeta')
                    ->insert([
                        'donation_id' => $donationId,
                        'meta_key' => $metaKey,
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a donation', compact('donation'));

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        $donation->id = $donationId;

        $donation->createdAt = $dateCreated;

        if (!isset($donation->updatedAt)) {
            $donation->updatedAt = $donation->createdAt;
        }

        if (!isset($donation->formTitle)) {
            $donation->formTitle = $this->getFormTitle($donation->formId);
        }

        if (!isset($donation->purchaseKey)) {
            $donation->purchaseKey = $donationMeta[DonationMetaKeys::PURCHASE_KEY];
        }

        Hooks::doAction('give_donation_created', $donation);
    }

    /**
     * @unreleased return void
     * @since 2.19.6
     *
     * @param Donation $donation
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function update(Donation $donation)
    {
        $this->validateDonation($donation);

        Hooks::doAction('give_donation_updating', $donation);

        $date = Temporal::getCurrentFormattedDateForDatabase();

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->where('ID', $donation->id)
                ->update([
                    'post_modified' => $date,
                    'post_modified_gmt' => get_gmt_from_date($date),
                    'post_status' => $donation->status->getValue(),
                    'post_type' => 'give_payment',
                    'post_parent' => isset($donation->parentId) ? $donation->parentId : 0
                ]);

            foreach ($this->getCoreDonationMetaForDatabase($donation) as $metaKey => $metaValue) {
                DB::table('give_donationmeta')
                    ->where('donation_id', $donation->id)
                    ->where('meta_key', $metaKey)
                    ->update([
                        'meta_value' => $metaValue,
                    ]);
            }
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donation', compact('donation'));

            throw new $exception('Failed updating a donation');
        }

        DB::query('COMMIT');

        Hooks::doAction('give_donation_updated', $donation);
    }

    /**
     * @unreleased consolidate meta deletion into a single query
     * @since 2.19.6
     *
     * @param Donation $donation
     * @return bool
     * @throws Exception
     */
    public function delete(Donation $donation)
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('give_donation_deleting', $donation);

        try {
            DB::table('posts')
                ->where('id', $donation->id)
                ->delete();

            DB::table('give_donationmeta')
                ->where('donation_id', $donation->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a donation', compact('donation'));

            throw new $exception('Failed deleting a donation');
        }

        DB::query('COMMIT');

        Hooks::doAction('give_donation_deleted', $donation);

        return true;
    }

    /**
     * @since 2.19.6
     *
     * @param Donation $donation
     *
     * @return array
     */
    private function getCoreDonationMetaForDatabase(Donation $donation)
    {
        $meta = [
            DonationMetaKeys::AMOUNT => Money::of($donation->amount, $donation->currency)->getAmount(),
            DonationMetaKeys::CURRENCY => $donation->currency,
            DonationMetaKeys::GATEWAY => $donation->gateway,
            DonationMetaKeys::DONOR_ID => $donation->donorId,
            DonationMetaKeys::FIRST_NAME => $donation->firstName,
            DonationMetaKeys::LAST_NAME => $donation->lastName,
            DonationMetaKeys::EMAIL => $donation->email,
            DonationMetaKeys::FORM_ID => $donation->formId,
            DonationMetaKeys::FORM_TITLE => isset($donation->formTitle) ? $donation->formTitle : $this->getFormTitle(
                $donation->formId
            ),
            DonationMetaKeys::MODE => isset($donation->mode) ? $donation->mode->getValue() : $this->getDefaultDonationMode()->getValue(),
            DonationMetaKeys::PURCHASE_KEY => isset($donation->purchaseKey)
                ? $donation->purchaseKey
                : Call::invoke(
                    GeneratePurchaseKey::class,
                    $donation->email
                ),
            DonationMetaKeys::DONOR_IP => isset($donation->donorIp) ? $donation->donorIp : give_get_ip(),
        ];

        if (isset($donation->billingAddress)) {
            $meta[DonationMetaKeys::BILLING_COUNTRY] = $donation->billingAddress->country;
            $meta[DonationMetaKeys::BILLING_ADDRESS2] = $donation->billingAddress->address2;
            $meta[DonationMetaKeys::BILLING_CITY] = $donation->billingAddress->city;
            $meta[DonationMetaKeys::BILLING_ADDRESS1] = $donation->billingAddress->address1;
            $meta[DonationMetaKeys::BILLING_STATE] = $donation->billingAddress->state;
            $meta[DonationMetaKeys::BILLING_ZIP] = $donation->billingAddress->zip;
        }

        if (isset($donation->subscriptionId)) {
            $meta[DonationMetaKeys::SUBSCRIPTION_ID] = $donation->subscriptionId;
        }

        if (isset($donation->anonymous)) {
            $meta[DonationMetaKeys::ANONYMOUS] = $donation->anonymous;
        }

        if (isset($donation->levelId)) {
            $meta[DonationMetaKeys::LEVEL_ID] = $donation->levelId;
        }

        return $meta;
    }

    /**
     * In Legacy terms, the Initial Donation acts as the parent ID for subscription renewals.
     * This function inserts those specific meta columns that accompany this concept.
     *
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function updateLegacyDonationMetaAsInitialSubscriptionDonation($donationId)
    {
        DB::query('START TRANSACTION');

        try {
            DB::table('give_donationmeta')
                ->insert(
                    [
                        'donation_id' => $donationId,
                        'meta_key' => '_give_subscription_payment',
                        'meta_value' => true,
                    ]
                );

            DB::table('give_donationmeta')
                ->insert(
                    [
                        'donation_id' => $donationId,
                        'meta_key' => '_give_is_donation_recurring',
                        'meta_value' => true,
                    ]
                );
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a donation as initial legacy subscription donation', compact('donationId'));

            throw new $exception('Failed updating a donation as initial legacy subscription donation');
        }

        DB::query('COMMIT');

        return true;
    }

    /**
     *
     * @since 2.19.6
     *
     * @param int $donationId
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
     * @since 2.19.6
     *
     * @param int $id
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
     * @since 2.19.6
     *
     * @param Donation $donation
     * @return void
     */
    private function validateDonation(Donation $donation)
    {
        foreach ($this->requiredDonationProperties as $key) {
            if (!isset($donation->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }

        if (!$donation->donor) {
            throw new InvalidArgumentException("Invalid donorId, Donor does not exist");
        }
    }

    /**
     * @since 2.19.6
     *
     * @return DonationMode
     */
    private function getDefaultDonationMode()
    {
        $mode = give_is_test_mode() ? 'test' : 'live';

        return new DonationMode($mode);
    }

    /**
     * @since 2.19.6
     *
     * @param int $formId
     * @return string
     */
    public function getFormTitle($formId)
    {
        $form = DB::table('posts')
            ->where('id', $formId)
            ->get();

        if (!$form) {
            return '';
        }

        return $form->post_title;
    }

    /**
     * @return ModelQueryBuilder<Donation>
     */
    public function prepareQuery()
    {
        $builder = new ModelQueryBuilder(Donation::class);

        return $builder->from('posts')
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
                ...DonationMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('post_type', 'give_payment');
    }

    /**
     * @since 2.19.6
     *
     * @param $donorId
     * @return int
     */
    public function getTotalDonationCountByDonorId($donorId)
    {
        return (int)DB::table('posts')
            ->where('post_type', 'give_payment')
            ->whereIn('ID', function (QueryBuilder $builder) use ($donorId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', DonationMetaKeys::DONOR_ID)
                    ->where('meta_value', $donorId);
            })
            ->count();
    }

    /**
     * @since 2.19.6
     *
     * @param $donorId
     * @return array|bool|null
     */
    public function getAllDonationIdsByDonorId($donorId)
    {
        return array_column(
            DB::table('give_donationmeta')
                ->select('donation_id')
                ->where('meta_key', DonationMetaKeys::DONOR_ID)
                ->where('meta_value', $donorId)
                ->getAll(),
            'donation_id'
        );
    }


    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return array
     */
    public function getDonationsForRequest(WP_REST_Request $request)
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('perPage');

        $query = DB::table('posts')
            ->distinct()
            ->select(
                'id',
                ['post_date', 'createdAt'],
                ['post_status', 'status']
            )
            ->attachMeta(
                'give_donationmeta',
                'id',
                'donation_id',
                DonationMetaKeys::FORM_ID,
                DonationMetaKeys::FORM_TITLE,
                DonationMetaKeys::AMOUNT,
                DonationMetaKeys::DONOR_ID,
                DonationMetaKeys::FIRST_NAME,
                DonationMetaKeys::LAST_NAME,
                DonationMetaKeys::EMAIL,
                DonationMetaKeys::GATEWAY,
                DonationMetaKeys::MODE,
                DonationMetaKeys::ANONYMOUS,
                DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION,
                DonationMetaKeys::IS_RECURRING
            )
            ->where('post_type', 'give_payment');

        $query = $this->getWhereConditionsForRequest($query, $request);

        $query->limit($perPage)
            ->orderBy('id', 'DESC')
            ->offset(($page - 1) * $perPage);

        $donations = $query->getAll();

        if (!$donations) {
            return [];
        }

        return $donations;
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return int
     */
    public function getTotalDonationsCountForRequest(WP_REST_Request $request)
    {
        $query = DB::table('posts')
            ->where('post_type', 'give_payment');

        $query = $this->getWhereConditionsForRequest($query, $request);

        return $query->count();
    }

    /**
     * @param QueryBuilder $query
     * @param WP_REST_Request $request
     * @return QueryBuilder
     * @unreleased
     *
     */
    private function getWhereConditionsForRequest(QueryBuilder $query, WP_REST_Request $request)
    {
        $search = $request->get_param('search');
        $start = $request->get_param('start');
        $end = $request->get_param('end');
        $form = $request->get_param('form');
        $donor = $request->get_param('donor');

        if ($form || $donor || ($search && !ctype_digit($search))) {
            $query->leftJoin(
                'give_donationmeta',
                'id',
                'metaTable.donation_id',
                'metaTable'
            );
        }

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                if (strpos($search, '@') !== false) {
                    $query
                        ->where('metaTable.meta_key', DonationMetaKeys::EMAIL)
                        ->whereLike('metaTable.meta_value', $search)
                    ;
                } else {
                    $query
                        ->where('metaTable.meta_key', DonationMetaKeys::FIRST_NAME)
                        ->whereLike('metaTable.meta_value', $search)
                        ->orWhere('metaTable.meta_key', DonationMetaKeys::LAST_NAME)
                        ->whereLike('metaTable.meta_value', $search);
                }
            }
        }

        if ($donor) {
            if (ctype_digit($donor)) {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::DONOR_ID)
                    ->where('metaTable.meta_value', $donor);
            } else {
                $query
                    ->where('metaTable.meta_key', DonationMetaKeys::FIRST_NAME)
                    ->whereLike('metaTable.meta_value', $donor)
                    ->orWhere('metaTable.meta_key', DonationMetaKeys::LAST_NAME)
                    ->whereLike('metaTable.meta_value', $donor);
            }
        }

        if ($form) {
            $query
                ->where('metaTable.meta_key', DonationMetaKeys::FORM_ID)
                ->where('metaTable.meta_value', $form);
        }

        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } else if ($start) {
            $query->where('post_date', $start, '>=');
        } else if ($end) {
            $query->where('post_date', $end, '<=');
        }

        return $query;
    }
}
