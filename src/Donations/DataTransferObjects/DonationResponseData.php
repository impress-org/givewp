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
 * @since 2.20.0
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
     * @var integer
     */
    public $formId;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var DonationMode
     */
    public $paymentMode;
    /**
     * @var bool
     */
    public $anonymous;
    /**
     * @var string
     */
    public $donationType;

    /**
     * Convert data from object to Donation
     *
     * @param object $donation
     *
     * @since 2.20.0
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
        $self->name = $donation->{DonationMetaKeys::FIRST_NAME()} . ' ' . $donation->{DonationMetaKeys::LAST_NAME()};
        $self->email = $donation->{DonationMetaKeys::EMAIL()};
        $self->gateway = give_get_gateway_admin_label($donation->{DonationMetaKeys::GATEWAY()});
        $self->createdAt = Date::getDateTime($donation->createdAt);
        $self->status = new DonationStatus($donation->status);
        $self->paymentMode = $donation->{DonationMetaKeys::MODE()};
        $self->anonymous = (bool)$donation->{DonationMetaKeys::ANONYMOUS()};
        $self->donationType = self::getDonationType($donation);

        return $self;
    }

    /**
     * Convert DTO to array
     *
     * @since 2.20.0
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Get donation type to display on front-end
     *
     * @since 2.20.0
     * @param object $donation
     * @return string
     */
    private static function getDonationType($donation)
    {
        if ($donation->{DonationMetaKeys::IS_RECURRING()}) {
            if ($donation->{DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION()}) {
                return 'subscription';
            }
            return 'renewal';
        }
        return 'single';
    }
}
