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
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donation
 *
 * @unreleased update amount type, fee recovered, and exchange rate
 * @since 2.19.6
 *
 * @property int $id
 * @property int $formId
 * @property string $formTitle
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property DonationStatus $status
 * @property DonationMode $mode
 * @property Money $amount amount charged to the gateway
 * @property Money $feeAmountRecovered
 * @property string $exchangeRate
 * @property string $gatewayId
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
        'amount' => Money::class,
        'feeAmountRecovered' => Money::class,
        'exchangeRate' => ['string', '1'],
        'gatewayId' => 'string',
        'donorId' => 'int',
        'firstName' => 'string',
        'lastName' => 'string',
        'email' => 'string',
        'parentId' => ['int', 0],
        'subscriptionId' => ['int', 0],
        'billingAddress' => BillingAddress::class,
        'anonymous' => ['bool', false],
        'levelId' => ['int', 0],
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
     * @since 2.19.6
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
     * @unreleased return mutated model instance
     * @since 2.19.6
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

        give()->donations->insert($donation);

        return $donation;
    }

    /**
     * @unreleased mutate model in repository and return void
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            give()->donations->insert($this);
        }

        give()->donations->update($this);
    }

    /**
     * @since 2.19.6
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
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donor>
     */
    public function donor()
    {
        return give()->donors->queryById($this->donorId);
    }

    /**
     * @since 2.19.6
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
     * @since 2.19.6
     *
     * @return int|null
     */
    public function getSequentialId()
    {
        return give()->donations->getSequentialId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return object[]
     */
    public function getNotes()
    {
        return give()->donations->getNotesByDonationId($this->id);
    }

    /**
     * Returns the amount charged in the currency the GiveWP site is set to
     *
     * @unreleased
     *
     * @return Money
     */
    public function amountInBaseCurrency()
    {
        return $this->amount->inBaseCurrency($this->exchangeRate);
    }

    /**
     * Returns the donation amount the donor "intended", which means it is the amount without recovered fees. So if the
     * donor paid $100, but the donation was charged $105 with a $5 fee, this method will return $100.
     *
     * @unreleased
     *
     * @return Money
     */
    public function intendedAmount()
    {
        return $this->feeAmountRecovered === null
            ? $this->amount
            : $this->amount->subtract($this->feeAmountRecovered);
    }

    /**
     * Returns the amount intended in the currency the GiveWP site is set to
     *
     * @unreleased
     *
     * @return Money
     */
    public function intendedAmountInBaseCurrency()
    {
        return $this->intendedAmount()->inBaseCurrency($this->exchangeRate);
    }

    /**
     * Returns the gateway instance for this donation
     *
     * @unreleased
     */
    public function gateway(): PaymentGateway
    {
        return give()->gateways->getPaymentGateway($this->gatewayId);
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    protected function getPropertyDefaults()
    {
        return array_merge(parent::getPropertyDefaults(), [
            'mode' => give_is_test_mode() ? DonationMode::TEST() : DonationMode::LIVE(),
            'donorIp' => give_get_ip(),
            'billingAddress' => new BillingAddress(),
        ]);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donation>
     */
    public static function query()
    {
        return give()->donations->prepareQuery();
    }

    /**
     * @since 2.19.6
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
