<?php

namespace Give\Donors\Models;

use Give\Donations\Models\Donation;
use Give\Subscriptions\Models\Subscription;

/**
 * Class Donor
 *
 * @unreleased
 */
class Donor
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $createdAt;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $email;

    /**
     * @unreleased
     *
     * @param $id
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
