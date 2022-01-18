<?php

namespace Give\Donors\Models;

use Give\Donors\Repositories\DonorRepository;

/**
 * Class Conor
 *
 * @unreleased
 *
 * @property int $id
 * @property string $created_at
 * @property string $name
 * @property string $email
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
    public $user_id;
    /**
     * @var string
     */
    public $created_at;
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
