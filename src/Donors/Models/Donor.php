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
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donor
 *
 * @since 2.24.0 add new properties $totalAmountDonated and $totalNumberOfDonations
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
 * @property Money $totalAmountDonated
 * @property int $totalNumberOfDonations
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
        'userId' => ['int', 0],
        'createdAt' => DateTime::class,
        'name' => 'string',
        'firstName' => 'string',
        'lastName' => 'string',
        'email' => 'string',
        'prefix' => 'string',
        'additionalEmails' => ['array', []],
        'totalAmountDonated' => Money::class,
        'totalNumberOfDonations' => 'int'
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
     * @return Donor|null
     */
    public static function find($id)
    {
        return give()->donors->getById($id);
    }

    /**
     * @since 2.19.6
     *
     * @return Donor|null
     */
    public static function whereEmail(string $email)
    {
        return give()->donors->getByEmail($email);
    }

    /**
     * @since 2.19.6
     *
     * @param  string  $donorEmail
     * @return bool
     */
    public function hasEmail(string $donorEmail): bool
    {
        $emails = array_merge($this->additionalEmails ?? [], [$this->email]);

        return in_array($donorEmail, $emails, true);
    }

    /**
     * @since 2.21.0
     *
     * @param  int  $userId
     * @return Donor|null
     */
    public static function whereUserId(int $userId)
    {
        return give()->donors->getByWpUserId($userId);
    }

    /**
     * @since 2.20.0 return mutated model instance
     * @since 2.19.6
     *
     * @param array $attributes
     *
     * @return Donor
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): Donor
    {
        $donor = new static($attributes);

        give()->donors->insert($donor);

        return $donor;
    }

    /**
     * @since 2.20.0 mutate model and return void
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            give()->donors->insert($this);
        } else {
            give()->donors->update($this);
        }
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
    public function donations(): ModelQueryBuilder
    {
        return give()->donations->queryByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function subscriptions(): ModelQueryBuilder
    {
        return give()->subscriptions->queryByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return int
     */
    public function totalDonations(): int
    {
        return give()->donations->getTotalDonationCountByDonorId($this->id);
    }

    /**
     * @since 2.19.6
     *
     * @return int
     */
    public function totalAmountDonated(): int
    {
        return array_sum(array_column($this->donations, DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()));
    }

    /**
     * @since 2.19.6
     *
     * @return ModelQueryBuilder<Donor>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->donors->prepareQuery();
    }

    /**
     * @since 2.19.6
     *
     * @param object $object
     *
     * @return Donor
     */
    public static function fromQueryBuilderObject($object): Donor
    {
        return DonorQueryData::fromObject($object)->toDonor();
    }

    /**
     * @since 2.19.6
     *
     * @return DonorFactory
     */
    public static function factory(): DonorFactory
    {
        return new DonorFactory(static::class);
    }

}
