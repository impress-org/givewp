<?php

namespace Give\DonationForms\V2\DataTransferObjects;

use DateTime;
use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\V2\Properties\DonationFormLevel;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonationFormQueryData
 *
 * @since 2.24.0
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
     * @var DonationFormLevel[]
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
     * @since 2.24.0
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
        $self->levels = $self->getDonationFormLevels($object);
        $self->goalOption = ($object->{DonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase()} === 'enabled');
        $self->createdAt = Temporal::toDateTime($object->createdAt);
        $self->updatedAt = Temporal::toDateTime($object->updatedAt);
        $self->totalAmountDonated = Money::fromDecimal($object->{DonationFormMetaKeys::FORM_EARNINGS()->getKeyAsCamelCase()}, give_get_currency());
        $self->totalNumberOfDonations = (int)$object->{DonationFormMetaKeys::FORM_SALES()->getKeyAsCamelCase()};
        $self->status = new DonationFormStatus($object->status);

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
     * @since 2.24.0
     *
     * @param $object
     *
     * @return DonationFormLevel[]
     */
    public function getDonationFormLevels($object): array
    {
        switch( $object->{DonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase()} ) {
            case 'multi':
                $levels = maybe_unserialize($object->{DonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase()});

                if (empty($levels)) {
                    return [];
                }

                return array_map(static function ($level) {
                    return DonationFormLevel::fromArray($level);
                }, $levels);
            case 'set':
                $amount = $object->{DonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase()};

                if (empty($amount)) {
                    return [];
                }

                return [
                    DonationFormLevel::fromPrice($amount),
                ];
            default:
                return [];
        }
    }
}
