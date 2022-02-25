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
     * @var int
     */
    private $levelId;

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
        $self->formId = (int)$donationQueryObject->{DonationMetaKeys::FORM_ID()->getKeyAsCamelCase()};
        $self->formTitle = $donationQueryObject->{DonationMetaKeys::FORM_TITLE()->getKeyAsCamelCase()};
        $self->amount = (int)$donationQueryObject->{DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()};
        $self->currency = $donationQueryObject->{DonationMetaKeys::CURRENCY()->getKeyAsCamelCase()};
        $self->donorId = (int)$donationQueryObject->{DonationMetaKeys::DONOR_ID()->getKeyAsCamelCase()};
        $self->firstName = $donationQueryObject->{DonationMetaKeys::FIRST_NAME()->getKeyAsCamelCase()};
        $self->lastName = $donationQueryObject->{DonationMetaKeys::LAST_NAME()->getKeyAsCamelCase()};
        $self->email = $donationQueryObject->{DonationMetaKeys::DONOR_EMAIL()->getKeyAsCamelCase()};
        $self->gateway = $donationQueryObject->{DonationMetaKeys::GATEWAY()->getKeyAsCamelCase()};
        $self->createdAt = $self->toDateTime($donationQueryObject->createdAt);
        $self->updatedAt = $self->toDateTime($donationQueryObject->updatedAt);
        $self->status = new DonationStatus($donationQueryObject->status);
        $self->parentId = (int)$donationQueryObject->parentId;
        $self->subscriptionId = (int)$donationQueryObject->{DonationMetaKeys::SUBSCRIPTION_ID()->getKeyAsCamelCase()};
        $self->mode = new DonationMode($donationQueryObject->{DonationMetaKeys::MODE()->getKeyAsCamelCase()});
        $self->billingAddress = BillingAddress::fromArray([
            'country' => $donationQueryObject->{DonationMetaKeys::BILLING_COUNTRY()->getKeyAsCamelCase()},
            'city' => $donationQueryObject->{DonationMetaKeys::BILLING_CITY()->getKeyAsCamelCase()},
            'state' => $donationQueryObject->{DonationMetaKeys::BILLING_STATE()->getKeyAsCamelCase()},
            'zip' => $donationQueryObject->{DonationMetaKeys::BILLING_ZIP()->getKeyAsCamelCase()},
            'address1' => $donationQueryObject->{DonationMetaKeys::BILLING_ADDRESS1()->getKeyAsCamelCase()},
            'address2' => $donationQueryObject->{DonationMetaKeys::BILLING_ADDRESS2()->getKeyAsCamelCase()},
        ]);
        $self->purchaseKey = $donationQueryObject->{DonationMetaKeys::PURCHASE_KEY()->getKeyAsCamelCase()};
        $self->donorIp = $donationQueryObject->{DonationMetaKeys::DONOR_IP()->getKeyAsCamelCase()};
        $self->anonymous = (bool)$donationQueryObject->{DonationMetaKeys::ANONYMOUS()->getKeyAsCamelCase()};
        $self->levelId = (int)$donationQueryObject->{DonationMetaKeys::LEVEL_ID()->getKeyAsCamelCase()};

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
