<?php

namespace Give\Donations\Models;

use Give\Donations\Repositories\DonationRepository;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;

/**
 * Class Donation
 *
 * @unreleased
 *
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $status
 * @property int $amount
 * @property string $currency
 * @property string $gateway
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 */
class Donation
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $createdAt;
    /**
     * @var string
     */
    public $updatedAt;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var string
     */
    public $gateway;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $email;

    /**
     * Find donation by ID
     *
     * @unreleased
     *
     * @param int $id
     * @return Donation
     */
    public function find($id)
    {
        return  give(DonationRepository::class)->getById($id);
    }

    /**
     * @return Donor
     */
    public function donor()
    {
        return give(DonorRepository::class)->getById($this->donorId);
    }

}
