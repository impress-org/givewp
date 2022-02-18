<?php

namespace Give\Donors\Models;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Model;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donor
 *
 * @unreleased
 *
 * @property int $id
 * @property int $userId
 * @property DateTime $createdAt
 * @property string $name
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 */
class Donor extends Model
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
     * @return Donation[]
     */
    public function donations()
    {
        return give()->donations->getByDonorId($this->id);
    }

    /**
     * @unreleased
     *
     * @return Subscription[]
     */
    public function subscriptions()
    {
        return give()->subscriptions->getByDonorId($this->id);
    }

}
