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
use Give\Subscriptions\DataTransferObjects\SubscriptionQueryData;
use Give\Subscriptions\Factories\SubscriptionFactory;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * Class Subscription
 *
 * @unreleased
 *
 * @property int $id
 * @property int $donationFormId
 * @property DateTime $createdAt
 * @property int $donorId
 * @property SubscriptionPeriod $period
 * @property int $frequency
 * @property int $installments
 * @property string $transactionId
 * @property int $amount
 * @property int $feeAmount
 * @property SubscriptionStatus $status
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
        'installments' => 'int',
        'transactionId' => 'string',
        'amount' => 'int',
        'feeAmount' => 'int',
        'status' => SubscriptionStatus::class,
        'gatewaySubscriptionId' => 'string',
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donor' => Relationship::BELONGS_TO,
        'donations' => Relationship::HAS_MANY
    ];

    /**
     * Find subscription by ID
     *
     * @unreleased
     *
     * @param  int  $id
     *
     * @return Subscription|null
     */
    public static function find($id)
    {
        return give()->subscriptions->getById($id);
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
     * @return ModelQueryBuilder<Donation>
     */
    public function donations()
    {
        return give()->donations->queryBySubscriptionId($this->id);
    }

    /**
     * Get Subscription notes
     *
     * @unreleased
     *
     * @return object[]
     */
    public function getNotes()
    {
        return give()->subscriptions->getNotesBySubscriptionId($this->id);
    }


    /**
     * @unreleased
     *
     * @throws Exception
     */
    public static function create(array $attributes)
    {
        $subscription = new static($attributes);

        return give()->subscriptions->insert($subscription);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function save()
    {
        return give()->subscriptions->update($this);
    }

    /**
     * @unreleased
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        return give()->subscriptions->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public static function query()
    {
        return give()->subscriptions->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @param  object  $object
     * @return Subscription
     */
    public static function fromQueryBuilderObject($object)
    {
        return SubscriptionQueryData::fromObject($object)->toSubscription();
    }


    /**
     * Expiration / End Date / Renewal
     *
     * @unreleased
     *
     * @return string
     */
    public function expiration()
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
     * @return SubscriptionFactory
     */
    public static function factory()
    {
        return new SubscriptionFactory(static::class);
    }
}
