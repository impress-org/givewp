<?php

namespace Give\Donors\DataTransferObjects;

use Give\Donors\Models\Donor;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * Class DonorObjectData
 *
 * @unreleased
 */
class DonorQueryData
{

    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $createdAt;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;

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

        $self->id = (int)$object->id;
        $self->userId = (int)$object->userId;
        $self->email = $object->email;
        $self->name = $object->name;
        $self->firstName = $object->firstName;
        $self->lastName = $object->lastName;
        $self->createdAt = Temporal::toDateTime($object->createdAt);

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donor
     */
    public function toDonor()
    {
        $attributes = get_object_vars($this);

        return new Donor($attributes);
    }
}
