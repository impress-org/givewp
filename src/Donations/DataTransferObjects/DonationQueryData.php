<?php

namespace Give\Donations\DataTransferObjects;

use DateTime;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Models\Traits\InteractsWithTime;

/**
 * Class DonationData
 *
 * @unreleased
 */
class DonationQueryData
{
    use InteractsWithTime;
    /**
     * @var int
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var int
     */
    private $donorId;
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var string
     */
    private $email;
    /**
     * @var int
     */
    private $id;
    /**
     * @var DonationStatus
     */
    private $status;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var int
     */
    private $subscriptionId;
    /**
     * @var DateTime
     */
    private $updatedAt;
    /**
     * @var DateTime
     */
    private $createdAt;
    /**
     * @var string
     */
    private $gateway;
    /**
     * @var DonationMode
     */
    private $mode;
    /**
     * @var int
     */
    private $formId;
    /**
     * @var BillingAddress
     */
    private $billingAddress;
    /**
     * @var string
     */
    private $formTitle;
    /**
     * @var string
     */
    private $purchaseKey;
    /**
     * @var string
     */
    private $donorIp;
    /**
     * @var bool
     */
    private $anonymous;

    /**
     * Convert data from object to Donation
     *
     * @param  object  $donationQueryObject
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromObject($donationQueryObject)
    {
        $self = new static();

        $self->id = (int)$donationQueryObject->id;
        $self->formId = (int)$donationQueryObject->{DonationMetaKeys::FORM_ID};
        $self->formTitle = $donationQueryObject->{DonationMetaKeys::FORM_TITLE};
        $self->amount = (int)$donationQueryObject->{DonationMetaKeys::TOTAL};
        $self->currency = $donationQueryObject->{DonationMetaKeys::CURRENCY};
        $self->donorId = (int)$donationQueryObject->{DonationMetaKeys::DONOR_ID};
        $self->firstName = $donationQueryObject->{DonationMetaKeys::BILLING_FIRST_NAME};
        $self->lastName = $donationQueryObject->{DonationMetaKeys::BILLING_LAST_NAME};
        $self->email = $donationQueryObject->{DonationMetaKeys::DONOR_EMAIL};
        $self->gateway = $donationQueryObject->{DonationMetaKeys::GATEWAY};
        $self->createdAt = $self->toDateTime($donationQueryObject->createdAt);
        $self->updatedAt = $self->toDateTime($donationQueryObject->updatedAt);
        $self->status = new DonationStatus($donationQueryObject->status);
        $self->parentId = (int)$donationQueryObject->parentId;
        $self->subscriptionId = (int)$donationQueryObject->{DonationMetaKeys::SUBSCRIPTION_ID};
        $self->mode = new DonationMode($donationQueryObject->{DonationMetaKeys::PAYMENT_MODE});
        $self->billingAddress = BillingAddress::fromArray([
            'country' => $donationQueryObject->{DonationMetaKeys::BILLING_COUNTRY},
            'city' => $donationQueryObject->{DonationMetaKeys::BILLING_CITY},
            'state' => $donationQueryObject->{DonationMetaKeys::BILLING_STATE},
            'zip' => $donationQueryObject->{DonationMetaKeys::BILLING_ZIP},
            'address1' => $donationQueryObject->{DonationMetaKeys::BILLING_ADDRESS1},
            'address2' => $donationQueryObject->{DonationMetaKeys::BILLING_ADDRESS2},
        ]);
        $self->purchaseKey = $donationQueryObject->{DonationMetaKeys::PURCHASE_KEY};
        $self->donorIp = $donationQueryObject->{DonationMetaKeys::DONOR_IP};
        $self->anonymous = (bool)$donationQueryObject->{DonationMetaKeys::ANONYMOUS_DONATION};

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donation
     */
    public function toDonation()
    {
        $attributes = get_object_vars($this);

        return new Donation($attributes);
    }
}
