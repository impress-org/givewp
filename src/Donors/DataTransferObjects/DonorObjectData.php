<?php

namespace Give\Donors\DataTransferObjects;

use Give\Donors\Models\Donor;

/**
 * Class DonorObjectData
 *
 * @unreleased
 */
class DonorObjectData
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $created_at;
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $name;

    /**
     * Convert data from donor object to Donor Model
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($object)
    {
        $self = new static();

        $self->id = $object->id;
        $self->user_id = $object->user_id;
        $self->email = $object->email;
        $self->name = $object->name;
        $self->created_at = $object->date_created;

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donor
     */
    public function toDonor()
    {
        $donor =  new Donor();

        $donor->id = $this->id;
        $donor->user_id = $this->user_id;
        $donor->created_at = $this->created_at;
        $donor->name = $this->name;
        $donor->email = $this->email;

        return $donor;
    }
}
