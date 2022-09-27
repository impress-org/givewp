<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use DateTimeInterface;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\ValueObjects\DonationFormMetaKeys;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\Framework\Blocks\BlockCollection;

class DonationFormQueryData
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
    public $settings;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * @var DateTimeInterface
     */
    public $updatedAt;

    /**
     * @var DonationFormStatus
     */
    public $status;

    /**
     * @var BlockCollection
     */
    public $blocks;

    /**
     * Convert data from object to Donation Form
     *
     * @unreleased
     *
     * @param  object  $queryObject
     *
     * @return DonationFormQueryData
     */
    public static function fromObject($queryObject): DonationFormQueryData
    {
        $self = new static();
        $self->id = (int)$queryObject->id;
        $self->title = $queryObject->title;
        $self->createdAt = Temporal::toDateTime($queryObject->createdAt);
        $self->updatedAt = Temporal::toDateTime($queryObject->updatedAt);
        $self->status = new DonationFormStatus($queryObject->status);
        $self->settings = json_decode($queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()}, true);
        $self->blocks = BlockCollection::fromJson($queryObject->blocks);

        return $self;
    }

    /**
     * Convert DTO to Donation Form
     *
     * @return DonationForm
     */
    public function toDonationForm(): DonationForm
    {
        $attributes = get_object_vars($this);

        return new DonationForm($attributes);
    }
}
