<?php

namespace Give\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\DataTransferObjects\SubscriptionQueryData;
use Give\Subscriptions\Factories\SubscriptionFactory;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class Subscription
 *
 * @since 2.19.6
 *
 * @property int $id
 * @property int $donationFormId
 * @property DateTime $createdAt
 * @property int $donorId
 * @property SubscriptionPeriod $period
 * @property int $frequency
 * @property int $installments
 * @property string $transactionId
 * @property Money $amount
 * @property Money $feeAmountRecovered
 * @property SubscriptionStatus $status
 * @property string $gatewayId
 * @property string $gatewaySubscriptionId
 * @property Donor $donor
 * @property Donation[] $donations
 */
class Subscription extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'donationFormId' => 'int',
        'createdAt' => DateTime::class,
        'donorId' => 'int',
        'period' => SubscriptionPeriod::class,
        'frequency' => 'int',
        'installments' => ['int', 0],
        'transactionId' => 'string',
        'amount' => Money::class,
        'feeAmountRecovered' => Money::class,
        'status' => SubscriptionStatus::class,
        'gatewaySubscriptionId' => ['string', ''],
        'gatewayId' => 'string',
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donor' => Relationship::BELONGS_TO,
        'donations' => Relationship::HAS_MANY,
    ];

    /**
     * Find subscription by ID
     *
     * @since 2.19.6
     *
     * @param int $id
     *
     * @return Subscription|null
     */
    public static function find($id)
    {
        return give()->subscriptions->getById($id);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donor>
     */
    public function donor(): ModelQueryBuilder
    {
        return give()->donors->queryById($this->donorId);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function donations(): ModelQueryBuilder
    {
        return give()->donations->queryBySubscriptionId($this->id);
    }

    /**
     * Get Subscription notes
     *
     * @since 2.19.6
     *
     * @return object[]
     */
    public function getNotes(): array
    {
        return give()->subscriptions->getNotesBySubscriptionId($this->id);
    }

    /**
     * Returns the subscription amount the donor "intended", which means it is the amount without recovered fees. So if the
     * donor paid $100, but the donation was charged $105 with a $5 fee, this method will return $100.
     *
     * @since 2.20.0
     */
    public function intendedAmount(): Money
    {
        return $this->feeAmountRecovered === null
            ? $this->amount
            : $this->amount->subtract($this->feeAmountRecovered);
    }

    /**
     * @since 2.20.0 return mutated model instance
     * @since 2.19.6
     *
     * @throws Exception
     */
    public static function create(array $attributes)
    {
        $subscription = new static($attributes);

        give()->subscriptions->insert($subscription);

        return $subscription;
    }

    /**
     * @since 2.20.0 mutate model in repository and return void
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function save()
    {
        if (!$this->id) {
            give()->subscriptions->insert($this);
        } else {
            give()->subscriptions->update($this);
        }
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete(): bool
    {
        return give()->subscriptions->delete($this);
    }

    /**
     * @since 2.20.0
     *
     * @param bool $force Set to true to ignore the status of the subscription
     *
     * @throws Exception
     */
    public function cancel(bool $force = false)
    {
        if (!$force && $this->status->isCanceled()) {
            return;
        }

        $this->gateway()->cancelSubscription($this);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->subscriptions->prepareQuery();
    }

    /**
     * @since 2.19.6
     *
     * @param object $object
     */
    public static function fromQueryBuilderObject($object): Subscription
    {
        return SubscriptionQueryData::fromObject($object)->toSubscription();
    }

    /**
     * Expiration / End Date / Renewal
     *
     * @since 2.19.6
     */
    public function expiration(): string
    {
        $frequency = $this->frequency;
        $period = $this->period;

        // Calculate the quarter as times 3 months
        if ($period->equals(SubscriptionPeriod::QUARTER())) {
            $frequency *= 3;
            $period = SubscriptionPeriod::MONTH();
        }

        return date('Y-m-d H:i:s', strtotime('+ ' . $frequency . $period->getValue() . ' 23:59:59'));
    }

    /**
     * @return PaymentGateway
     */
    public function gateway(): PaymentGateway
    {
        return give()->gateways->getPaymentGateway($this->gatewayId);
    }

    /**
     * @since 2.19.6
     */
    public static function factory(): SubscriptionFactory
    {
        return new SubscriptionFactory(static::class);
    }
}
