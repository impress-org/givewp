<?php

namespace Give\DonationForms\DataTransferObjects;

use DateTime;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\DonationFormLevel;
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
     * @param $object
     *
     * @return DonationFormQueryData
     */
    public static function fromObject($object): DonationFormQueryData
    {
        $self = new static();

        $self->id = (int)$object->id;
        $self->title = $object->title;
        $self->levels = $self->getDonationFormLevels( maybe_unserialize($object->{DonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()}));
        $self->goalOption = ($object->{DonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase()} === 'enabled');
        $self->createdAt = Temporal::toDateTime($object->createdAt);
        $self->updatedAt = Temporal::toDateTime($object->updatedAt);
        $self->totalAmountDonated = Money::fromDecimal($object->{DonationFormMetaKeys::FORM_EARNINGS()->getKeyAsCamelCase()}, give_get_currency());
        $self->totalNumberOfDonations = (int)$object->{DonationFormMetaKeys::FORM_SALES()->getKeyAsCamelCase()};
        $self->status = $object->status; // TODO: Implement DonationFormStatus class and replace this with to return an instance of DonationFormStatus

        return $self;
    }

    /**
     * Convert DTO to DonationForm
     *
     * @return DonationForm
     */
    public function toDonationForm(): DonationForm
    {
        $attributes = get_object_vars($this);

        return new DonationForm($attributes);
    }

    /**
     * @unreleased
     *
     * @param $array
     *
     * @return DonationFormLevel[]
     */
    public function getDonationFormLevels($array): array
    {
        $levels = [];

        foreach ($array as $level) {
            $levels[] = DonationFormLevel::fromArray($level);
        }

        return $levels;
    }
}
