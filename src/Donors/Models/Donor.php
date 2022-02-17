<?php

namespace Give\Donors\Models;

use DateTime;
use Give\Donations\Models\Donation;
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
