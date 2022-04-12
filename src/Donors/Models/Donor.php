<?php

namespace Give\Donors\Models;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\DataTransferObjects\DonorQueryData;
use Give\Donors\Factories\DonorFactory;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donor
 *
 * @since 2.19.6
 *
 * @property int $id
 * @property int $userId
 * @property DateTime $createdAt
 * @property string $name
 * @property string $prefix
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string[] $additionalEmails
 * @property Subscription[] $subscriptions
 * @property Donation[] $donations
 */
class Donor extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'userId' => 'int',
        'createdAt' => DateTime::class,
        'name' => 'string',
        'firstName' => 'string',
        'lastName' => 'string',
        'email' => 'string',
        'prefix' => 'string',
        'additionalEmails' => 'array',
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donations' => Relationship::HAS_MANY,
        'subscriptions' => Relationship::HAS_MANY,
    ];

    /**
     * @since 2.19.6
     *
     * @param $id
     *
     * @return Donor
     */
    public static function find($id)
    {
        return give()->donors->getById($id);
    }

    /**
     * @since 2.19.6
     *
     * @param  string  $email
     * @return Donor
     */
    public static function whereEmail($email)
    {
        return give()->donors->getByEmail($email);
    }

    /**
     * @since 2.19.6
     *
     * @param  int  $userId
     * @return Donor
     */
    public static function whereUserId($userId)
    {
        return give()->donors->getByWpUserId($userId);
    }

    /**
     * @since 2.19.6
     *
     * @param  array  $attributes
     *
     * @return Donor
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes)
    {
        $donor = new static($attributes);

        return give()->donors->insert($donor);
    }

    /**
     * @since 2.19.6
     *
     * @return Donor
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            return give()->donors->insert($this);
        }

        return give()->donors->update($this);
    }

    /**
     * @since 2.19.6
     *
     * @throws Exception
     */
    public function delete()
    {
        return give()->donors->delete($this);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function donations()
    {
        return give()->donations->queryByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function subscriptions()
    {
        return give()->subscriptions->queryByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return int
     */
    public function totalDonations()
    {
        return give()->donations->getTotalDonationCountByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return int
     */
    public function totalAmountDonated()
    {
        return array_sum(array_column($this->donations, DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()));
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donor>
     */
    public static function query()
    {
        return give()->donors->prepareQuery();
    }

    /**
     * @since 2.19.6
     *
     * @param  object  $object
     * @return Donor
     */
    public static function fromQueryBuilderObject($object)
    {
        return DonorQueryData::fromObject($object)->toDonor();
    }

    /**
     * @return ModelFactory<Donor>
     */
    public static function factory()
    {
        return new DonorFactory(static::class);
    }

}
