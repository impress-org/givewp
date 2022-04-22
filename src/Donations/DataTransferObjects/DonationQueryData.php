<?php

namespace Give\Donations\DataTransferObjects;

use DateTime;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * Class DonationData
 *
 * @since 2.19.6
 */
class DonationQueryData
{
    /**
     * @var int
     */
    public $amount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var int
     */
    public $donorId;
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
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
     * @var int
     */
    public $parentId;
    /**
     * @var int
     */
    public $subscriptionId;
    /**
     * @var DateTime
     */
    public $updatedAt;
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
    public $mode;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var BillingAddress
     */
    public $billingAddress;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var string
     */
    public $purchaseKey;
    /**
     * @var string
     */
    public $donorIp;
    /**
     * @var bool
     */
    public $anonymous;
    /**
     * @var int
     */
    public $levelId;
    /**
     * @var string
     */
    public $gatewayTransactionId;

    /**
     * Convert data from object to Donation
     *
     * @param  object  $donationQueryObject
     *
     * @since 2.19.6
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
        $self->email = $donationQueryObject->{DonationMetaKeys::EMAIL()->getKeyAsCamelCase()};
        $self->gateway = $donationQueryObject->{DonationMetaKeys::GATEWAY()->getKeyAsCamelCase()};
        $self->createdAt = Temporal::toDateTime($donationQueryObject->createdAt);
        $self->updatedAt = Temporal::toDateTime($donationQueryObject->updatedAt);
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
        $self->gatewayTransactionId = $donationQueryObject->{DonationMetaKeys::GATEWAY_TRANSACTION_ID(
        )->getKeyAsCamelCase()};

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
