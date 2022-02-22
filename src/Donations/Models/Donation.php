<?php

namespace Give\Donations\Models;

use DateTime;
use Exception;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
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
 */
class Donation extends Model implements ModelCrud
{
    /**
     * @var string[]
     */
    protected $properties = [
        'id' => 'int',
        'formId' => 'int',
        'formTitle' => 'string',
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
        'billingAddress' => BillingAddress::class
    ];

    /**
     * Find donation by ID
     *
     * @unreleased
     *
     * @param  int  $id
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
     * TODO: add sequential ID
     *
     * @param  array  $attributes
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
     * @return Donor
     */
    public function donor()
    {
        return give()->donorRepository->getById($this->donorId);
    }

    /**
     * @unreleased
     *
     * @return Subscription
     */
    public function subscription()
    {
        if ($this->subscriptionId) {
            return give()->subscriptions->getById($this->subscriptionId);
        }

        return give()->subscriptions->getByDonationId($this->id);
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
}
