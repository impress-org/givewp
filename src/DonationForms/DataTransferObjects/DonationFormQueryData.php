<?php

namespace Give\DonationForms\DataTransferObjects;

use DateTimeInterface;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Support\Facades\DateTime\Temporal;

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
     * @since 3.0.0
     *
     * @param  object  $queryObject
     *
     * @return DonationFormQueryData
     */
    public static function fromObject($queryObject): self
    {
        $self = new self();
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
