<?php

namespace Give\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
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
 */
class Subscription extends Model implements ModelCrud
{
    /**
     * @var string[]
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
     * @return Donor|null
     */
    public function donor()
    {
        return give()->donorRepository->getById($this->donorId);
    }

    /**
     * @unreleased
     *
     * @return Donation[]
     */
    public function donations()
    {
        return give()->donations->getBySubscriptionId($this->id);
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
     * @throws Exception
     */
    public static function create(array $attributes)
    {
        $subscription = new static($attributes);

        return give()->subscriptions->insert($subscription);
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}
