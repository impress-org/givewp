<?php

namespace Give\Donors\Models;

use DateTime;
use Exception;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\DataTransferObjects\DonorQueryData;
use Give\Donors\Factories\DonorFactory;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Models\Model;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Class Donor
 *
 * @unreleased
 *
 * @property int $id
 * @property int $userId
 * @property DateTime $createdAt
 * @property string $name // TODO: name should be an accessor
 * @property string $prefix
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 */
class Donor extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * @var string[]
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
    ];

    /**
     * @unreleased
     *
     * @param $id
     *
     * @return Donor
     */
    public static function find($id)
    {
        return give()->donorRepository->getById($id);
    }

    /**
     * @unreleased
     *
     * @param  string  $email
     * @return Donor
     */
    public static function whereEmail($email)
    {
        return give()->donorRepository->getByEmail($email);
    }

    /**
     * @unreleased
     *
     * @param  string  $email
     * @return bool
     */
    public function hasEmail($email)
    {
        $donor = give()->donorRepository->getByEmail($email);

        return !is_null($donor);
    }

    /**
     * @unreleased
     *
     * @param  int  $userId
     * @return Donor
     */
    public static function whereUserId($userId)
    {
        return give()->donorRepository->getByWpUserId($userId);
    }

    /**
     * @unreleased
     *
     * @param  string  $email
     * @return bool
     * @throws Exception
     */
    public function addAdditionalEmail($email)
    {
        return give()->donorRepository->insertAdditionalEmail($this->id, $email);
    }

    /**
     * @unreleased
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

        return give()->donorRepository->insert($donor);
    }

    /**
     * @unreleased
     *
     * @return Donor
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (!$this->id) {
            return give()->donorRepository->insert($this);
        }

        return give()->donorRepository->update($this);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete()
    {
        return give()->donorRepository->delete($this);
    }

    /**
     * @unreleased
     *
     * @return QueryBuilder
     */
    public function donations()
    {
        return give()->donations->queryByDonorId($this->id);
    }

    /**
     * @unreleased
     *
     * @return QueryBuilder
     */
    public function subscriptions()
    {
        return give()->subscriptions->queryByDonorId($this->id);
    }

    /**
     * @unreleased
     *
     * @return array
     */
    public function additionalEmails()
    {
        return give()->donorRepository->getAdditionalEmails($this->id);
    }

    /**
     * @unreleased
     *
     * @return int
     */
    public function totalDonations()
    {
        return (int)give()->donations->getTotalDonationCountByDonorId($this->id);
    }

    /**
     * @unreleased
     *
     * @return int
     */
    public function totalAmountDonated()
    {
        return array_sum(array_column($this->donations()->getAll(), DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()));
    }

    /**
     * @unreleased
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        return give()->donorRepository->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @param  object  $object
     * @return Donor
     */
    public static function fromQueryBuilderObject($object)
    {
        return DonorQueryData::fromObject($object)->toDonor();
    }

    /**
     * @return ModelFactory
     */
    public static function factory()
    {
        return new DonorFactory(static::class);
    }

}
