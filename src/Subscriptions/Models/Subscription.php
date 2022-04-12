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
     * @since 2.19.6
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
     * @return ModelQueryBuilder<Donation>
     */
    public function donations()
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
    public function getNotes()
    {
        return give()->subscriptions->getNotesBySubscriptionId($this->id);
    }


    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public static function create(array $attributes)
    {
        $subscription = new static($attributes);

        return give()->subscriptions->insert($subscription);
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function save()
    {
        return give()->subscriptions->update($this);
    }

    /**
     * @since 2.19.6
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        return give()->subscriptions->delete($this);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public static function query()
    {
        return give()->subscriptions->prepareQuery();
    }

    /**
     * @since 2.19.6
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
     * @since 2.19.6
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
     * @return SubscriptionFactory<Subscription>
     */
    public static function factory()
    {
        return new SubscriptionFactory(static::class);
    }
}
