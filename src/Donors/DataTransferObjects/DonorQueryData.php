<?php

namespace Give\Donors\DataTransferObjects;

use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonorObjectData
 *
 * @since 2.19.6
 */
final class DonorQueryData
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
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var array
     */
    public $additionalEmails;
    /**
     * @var string
     */
    public $prefix;
    /**
     * @var Money
     */
    public $totalAmountDonated;
    /**
     * @var int
     */
    public $totalNumberOfDonations;

    /**
     * Convert data from donor object to Donor Model
     *
     * @since 3.7.0 Add "phone" property
     * @since 2.24.0 add $totalAmountDonated and $totalNumberOfDonations
     * @since 2.20.0 add donor prefix property
     * @since 2.19.6
     *
     * @return self
     */
    public static function fromObject($object)
    {
        $self = new static();

        $self->id = (int)$object->id;
        $self->userId = (int)$object->userId;
        $self->prefix = $object->{DonorMetaKeys::PREFIX()->getKeyAsCamelCase()};
        $self->email = $object->email;
        $self->phone = $object->phone;
        $self->name = $object->name;
        $self->firstName = $object->firstName;
        $self->lastName = $object->lastName;
        $self->createdAt = Temporal::toDateTime($object->createdAt);
        $self->additionalEmails = $object->additionalEmails;
        $self->totalAmountDonated = Money::fromDecimal($object->totalAmountDonated, give_get_currency());
        $self->totalNumberOfDonations = (int)$object->totalNumberOfDonations;

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
