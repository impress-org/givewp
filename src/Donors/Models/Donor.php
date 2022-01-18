<?php

namespace Give\Donors\Models;

use Give\Donors\Repositories\DonorRepository;

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
     * Find donation by ID
     *
     * @unreleased
     *
     * @param $id
     * @return Donor
     */
    public function find($id)
    {
        return give(DonorRepository::class)->getById($id);
    }

}
