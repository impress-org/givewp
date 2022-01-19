<?php

namespace Give\Donors\Models;

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
     * Find donor by ID
     *
     * @unreleased
     *
     * @param $id
     * @return Donor
     */
    public static function find($id)
    {
        return give()->donorRepository->getById($id);
    }

}
