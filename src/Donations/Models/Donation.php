<?php

namespace Give\Donations\Models;

use DateTime;
use Exception;
use Give\Donations\DataTransferObjects\DonationQueryData;
use Give\Donations\Factories\DonationFactory;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Subscriptions\Models\Subscription;
use Give\ValueObjects\Money;

/**
 * Class Donation
 *
 * @unreleased
 *
 * @property int $id
 * @property int $formId
 * @property string $formTitle
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property DonationStatus $status
 * @property DonationMode $mode
 * @property int $amount
 * @property string $currency
 * @property string $gateway
 * @property int $donorId
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property int $parentId
 * @property int $subscriptionId
 * @property BillingAddress $billingAddress
 * @property string $purchaseKey
 * @property string $donorIp
 * @property bool $anonymous
 * @property int $levelId
 * @property string $gatewayTransactionId
 * @property Donor $donor
 * @property Subscription $subscription
 */
class Donation extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'formId' => 'int',
        'formTitle' => 'string',
        'purchaseKey' => 'string',
        'donorIp' => 'string',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => DonationStatus::class,
        'mode' => DonationMode::class,
        'amount' => 'int',
        'currency' => 'string',
        'gateway' => 'string',
        'donorId' => 'int',
        'firstName' => 'string',
        'lastName' => 'string',
        'email' => 'string',
        'parentId' => 'int',
        'subscriptionId' => 'int',
        'billingAddress' => BillingAddress::class,
        'anonymous' => 'bool',
        'levelId' => 'int',
        'gatewayTransactionId' => 'string',
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donor' => Relationship::BELONGS_TO,
        'subscription' => Relationship::BELONGS_TO,
    ];

    /**
     * Find donation by ID
     *
     * @unreleased
     *
     * @param int $id
     *
     * @return Donation
     */
    public static function find($id)
    {
        return give()->donations->getById($id);
    }

    /**
     * @unreleased
     *
     * @param array $attributes
     *
     * @return Donation
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes)
    {
        $donation = new static($attributes);

        return give()->donations->insert($donation);
    }

    /**
     * @unreleased
     *
     * @return Donation
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            return give()->donations->insert($this);
        }

        return give()->donations->update($this);
    }

    /**
     * @unreleased
     *
     * @return bool
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete()
    {
        return give()->donations->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Donor>
     */
    public function donor()
    {
        return give()->donors->queryById($this->donorId);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function subscription()
    {
        if ($this->subscriptionId) {
            return give()->subscriptions->queryById($this->subscriptionId);
        }

        return give()->subscriptions->queryByDonationId($this->id);
    }

    /**
     * @unreleased
     *
     * @return int|null
     */
    public function getSequentialId()
    {
        return give()->donations->getSequentialId($this->id);
    }

    /**
     * @unreleased
     *
     * @return object[]
     */
    public function getNotes()
    {
        return give()->donations->getNotesByDonationId($this->id);
    }

    /**
     * @unreleased
     *
     * @return Money
     */
    public function getMinorAmount()
    {
        return Money::ofMinor($this->amount, $this->currency);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Donation>
     */
    public static function query()
    {
        return give()->donations->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @param object $object
     *
     * @return Donation
     */
    public static function fromQueryBuilderObject($object)
    {
        return DonationQueryData::fromObject($object)->toDonation();
    }

    /**
     * @return DonationFactory<Donation>
     */
    public static function factory()
    {
        return new DonationFactory(static::class);
    }
}
