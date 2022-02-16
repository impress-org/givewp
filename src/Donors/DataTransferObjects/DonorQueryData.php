<?php

namespace Give\Donors\DataTransferObjects;

use Give\Donors\Models\Donor;
use Give\Framework\Models\Traits\InteractsWithTime;

/**
 * Class DonorObjectData
 *
 * @unreleased
 */
class DonorQueryData
{
    use InteractsWithTime;

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
        $self->userId = (int)$object->user_id;
        $self->email = $object->email;
        $self->name = $object->name;
        $self->createdAt = $self->toDateTime($object->date_created);

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donor
     */
    public function toDonor()
    {
        return new Donor([
            'id' => $this->id,
            'userId' => $this->userId,
            'createdAt' => $this->createdAt,
            'name' => $this->name,
            'email' => $this->email
        ]);
    }
}
