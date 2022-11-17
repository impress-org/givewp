<?php

namespace Give\DonationForms\DataTransferObjects;

use DateTime;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonationFormQueryData
 *
 * @unreleased
 */
final class DonationFormQueryData
{

    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var array
     */
    public $levels;
    /**
     * @var boolean
     */
    public $goalOption;
    /**
     * @var int
     */
    public $totalNumberOfDonations;
    /**
     * @var Money
     */
    public $totalAmountDonated;
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var DateTime
     */
    public $updatedAt;
    /**
     * @var string
     */
    public $status;

    /**
     * Convert data from donation form object to DonationForm Model
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($object)
    {
        $self = new static();

        $self->id = (int)$object->id;
        $self->title = $object->title;
        $self->levels = maybe_unserialize($object->{DonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()}); // TODO: Implement DonationFormLevels class and replace this with DonationFormLevels
        $self->goalOption = ($object->{DonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase()} === 'enabled');
        $self->createdAt = Temporal::toDateTime($object->createdAt);
        $self->updatedAt = Temporal::toDateTime($object->updatedAt);
        $self->totalAmountDonated = Money::fromDecimal(0, give_get_currency()); // TODO: Implement query to get the total amount donated
        $self->totalNumberOfDonations = 0; // TODO: Implement query to get the total number of donations
        $self->status = $object->status; // TODO: Implement DonationFormStatus class and replace this with to return an instance of DonationFormStatus

        return $self;
    }

    /**
     * Convert DTO to DonationForm
     *
     * @return DonationForm
     */
    public function toDonationForm()
    {
        $attributes = get_object_vars($this);

        return new DonationForm($attributes);
    }
}
