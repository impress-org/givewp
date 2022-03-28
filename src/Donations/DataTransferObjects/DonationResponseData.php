<?php

namespace Give\Donations\DataTransferObjects;

use DateTime;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Helpers\Date;

/**
 * Class DonationResponseData
 *
 * @unreleased
 */
class DonationResponseData implements Arrayable
{
    /**
     * @var int
     */
    public $amount;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var string
     */
    public $donorName;
    /**
     * @var string
     */
    public $email;
    /**
     * @var int
     */
    public $id;
    /**
     * @var DonationStatus
     */
    public $status;
    /**
     * @var DateTime
     */
    public $createdAt;
    /**
     * @var string
     */
    public $gateway;
    /**
     * @var DonationMode
     */
    public $formId;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var bool
     */
    public $anonymous;

    /**
     * Convert data from object to Donation
     *
     * @param object $donation
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($donation)
    {
        $self = new static();

        $self->id = (int)$donation->id;
        $self->formId = (int)$donation->{DonationMetaKeys::FORM_ID()};
        $self->formTitle = $donation->{DonationMetaKeys::FORM_TITLE()};
        $self->amount = html_entity_decode(give_currency_filter(give_format_amount($donation->{DonationMetaKeys::AMOUNT()})));
        $self->donorId = (int)$donation->{DonationMetaKeys::DONOR_ID()};
        $self->donorName = $donation->{DonationMetaKeys::FIRST_NAME()} . ' ' . $donation->{DonationMetaKeys::LAST_NAME()};
        $self->email = $donation->{DonationMetaKeys::EMAIL()};
        $self->gateway = $donation->{DonationMetaKeys::GATEWAY()};
        $self->createdAt = Date::getDateTime($donation->createdAt);
        $self->status = new DonationStatus($donation->status);
        $self->anonymous = (bool)$donation->{DonationMetaKeys::ANONYMOUS()};

        return $self;
    }

    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
