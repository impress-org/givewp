<?php

namespace Give\Donations\Repositories;

use DateTimeInterface;
use Give\Donations\Actions\GeneratePurchaseKey;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Call;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @since 2.20.0 update amount type, fee recovered, and exchange rate
 * @since 2.19.6
 */
class DonationRepository
{
    /**
     * @var DonationNotesRepository
     */
    public $notes;

    /**
     * @since 2.21.0
     */
    public function __construct()
    {
        $this->notes = give(DonationNotesRepository::class);
    }

    /**
     * @since 2.19.6
     *
     * @var string[]
     */
    private $requiredDonationProperties = [
        'formId',
        'status',
        'gatewayId',
        'amount',
        'donorId',
        'firstName',
        'email',
    ];

    /**
     * Get Donation By ID
     *
     * @since 2.19.6
     *
     * @return Donation|null
     */
    public function getById(int $donationId)
    {
        return $this->prepareQuery()
            ->where('ID', $donationId)
            ->get();
    }

    /**
     * @since 2.21.0
     * @return Donation|null
     */
    public function getByGatewayTransactionId($gatewayTransactionId)
    {
        return $this->queryByGatewayTransactionId($gatewayTransactionId)->get();
    }

    /**
     * @since 2.21.0
     * @return ModelQueryBuilder
     */
    public function queryByGatewayTransactionId($gatewayTransactionId)
    {
        return $this->prepareQuery()
            ->where('post_type', 'give_payment')
            ->where('ID', function (QueryBuilder $builder) use ($gatewayTransactionId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', DonationMetaKeys::GATEWAY_TRANSACTION_ID()->getValue())
                    ->where('meta_value', $gatewayTransactionId);
            });
    }

    /**
     * @since 2.19.6
     *
     * @param  int  $donationId
     * @return ModelQueryBuilder<Donation>
     */
    public function queryById(int $donationId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->where('ID', $donationId);
    }

    /**
     * @since 2.19.6
     *
     * @return Donation[]|null
     */
    public function getBySubscriptionId(int $subscriptionId)
    {
        return $this->queryBySubscriptionId($subscriptionId)->getAll();
    }

    /**
     * @since 2.19.6
     *
     * @param  int  $subscriptionId
     * @return ModelQueryBuilder<Donation>
     */
    public function queryBySubscriptionId(int $subscriptionId): ModelQueryBuilder
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
     * @param  int  $donorId
     * @return ModelQueryBuilder<Donation>
     */
    public function queryByDonorId(int $donorId): ModelQueryBuilder
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
     *
     * @since 2.21.0 replace actions with givewp_donation_creating and givewp_donation_created
     * @since 2.20.0 mutate model and return void
     * @since 2.19.6
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Donation $donation)
    {
        $this->validateDonation($donation);

        Hooks::doAction('givewp_donation_creating', $donation);

        $dateCreated = Temporal::withoutMicroseconds($donation->createdAt ?: Temporal::getCurrentDateTime());
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
                    'post_parent' => $donation->parentId ?? 0
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

        Hooks::doAction('givewp_donation_created', $donation);
    }

    /**
     * @since 2.21.0 replace actions with givewp_donation_updating and givewp_donation_updated
     * @since 2.20.0 return void
     * @since 2.19.6
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     */
    public function update(Donation $donation)
    {
        $this->validateDonation($donation);

        Hooks::doAction('givewp_donation_updating', $donation);

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
                    'post_parent' => $donation->parentId ?? 0
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

        Hooks::doAction('givewp_donation_updated', $donation);
    }

    /**
     * @since 2.21.0 replace actions with givewp_donation_deleting and givewp_donation_deleted
     * @since 2.20.0 consolidate meta deletion into a single query
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete(Donation $donation): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_donation_deleting', $donation);

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

        Hooks::doAction('givewp_donation_deleted', $donation);

        return true;
    }

    /**
     * @since 2.20.0 update amount to use new type, and add currency and exchange rate
     * @since 2.19.6
     */
    private function getCoreDonationMetaForDatabase(Donation $donation): array
    {
        $meta = [
            DonationMetaKeys::GATEWAY_TRANSACTION_ID => $donation->gatewayTransactionId,
            DonationMetaKeys::AMOUNT => give_sanitize_amount_for_db(
                $donation->amount->formatToDecimal(),
                ['currency' => $donation->amount->getCurrency()]
            ),
            DonationMetaKeys::CURRENCY => $donation->amount->getCurrency()->getCode(),
            DonationMetaKeys::EXCHANGE_RATE => $donation->exchangeRate,
            DonationMetaKeys::GATEWAY => $donation->gatewayId,
            DonationMetaKeys::DONOR_ID => $donation->donorId,
            DonationMetaKeys::FIRST_NAME => $donation->firstName,
            DonationMetaKeys::LAST_NAME => $donation->lastName,
            DonationMetaKeys::EMAIL => $donation->email,
            DonationMetaKeys::FORM_ID => $donation->formId,
            DonationMetaKeys::FORM_TITLE => $donation->formTitle ?? $this->getFormTitle($donation->formId),
            DonationMetaKeys::MODE => isset($donation->mode) ?
                $donation->mode->getValue() :
                $this->getDefaultDonationMode()->getValue(),
            DonationMetaKeys::PURCHASE_KEY => $donation->purchaseKey ?? Call::invoke(
                    GeneratePurchaseKey::class,
                    $donation->email
                ),
            DonationMetaKeys::DONOR_IP => $donation->donorIp ?? give_get_ip(),
            DonationMetaKeys::LEVEL_ID => $donation->levelId,
            DonationMetaKeys::ANONYMOUS => (int)$donation->anonymous
        ];

        if ($donation->feeAmountRecovered !== null) {
            $meta[DonationMetaKeys::FEE_AMOUNT_RECOVERED] = $donation->feeAmountRecovered->formatToDecimal();
        }

        if ($donation->billingAddress !== null) {
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
    public function updateLegacyDonationMetaAsInitialSubscriptionDonation($donationId): bool
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
     * @since 2.19.6
     *
     * @return int|null
     */
    public function getSequentialId(int $donationId)
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
     * @return object[]
     */
    public function getNotesByDonationId(int $id): array
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
     */
    private function getDefaultDonationMode(): DonationMode
    {
        $mode = give_is_test_mode() ? 'test' : 'live';

        return new DonationMode($mode);
    }

    /**
     * @since 2.19.6
     */
    public function getFormTitle(int $formId): string
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
    public function prepareQuery(): ModelQueryBuilder
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
     */
    public function getTotalDonationCountByDonorId(int $donorId): int
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
     * @return array|bool|null
     */
    public function getAllDonationIdsByDonorId(int $donorId)
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
     * @since 2.21.2
     *
     * @return Donation|null
     */
    public function getFirstDonation() {
        return $this->prepareQuery()
            ->limit(1)
            ->orderBy('post_date', 'DESC')
            ->get();
    }

    /**
     * @since 2.21.2
     *
     * @return Donation|null
     */
    public function getLatestDonation() {
        return $this->prepareQuery()
            ->limit(1)
            ->orderBy('post_date', 'ASC')
            ->get();
    }
}
