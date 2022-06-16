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
     * @var string
     */
    public $name;

    /**
     * Convert data from object to Donation
     *
     * @param  object  $donation
     *
     * @since 2.21.0 use meta keys as camelcase
     * @since 2.20.0
     */
    public static function fromObject($donation): DonationResponseData
    {
        $self = new static();

        $self->id = (int)$donation->id;
        $self->formId = (int)$donation->{DonationMetaKeys::FORM_ID()->getKeyAsCamelCase()};
        $self->formTitle = $donation->{DonationMetaKeys::FORM_TITLE()->getKeyAsCamelCase()};
        $self->amount = html_entity_decode(
            give_currency_filter(give_format_amount($donation->{DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()}))
        );
        $self->donorId = (int)$donation->{DonationMetaKeys::DONOR_ID()->getKeyAsCamelCase()};
        $self->name = $donation->{DonationMetaKeys::FIRST_NAME()->getKeyAsCamelCase(
            )} . ' ' . $donation->{DonationMetaKeys::LAST_NAME()->getKeyAsCamelCase()};
        $self->email = $donation->{DonationMetaKeys::EMAIL()->getKeyAsCamelCase()};
        $self->gateway = give_get_gateway_admin_label($donation->{DonationMetaKeys::GATEWAY()->getKeyAsCamelCase()});
        $self->createdAt = Date::getDateTime($donation->createdAt);
        $self->status = new DonationStatus($donation->status);
        $self->paymentMode = $donation->{DonationMetaKeys::MODE()->getKeyAsCamelCase()};
        $self->anonymous = (bool)$donation->{DonationMetaKeys::ANONYMOUS()->getKeyAsCamelCase()};
        $self->donationType = self::getDonationType($donation);

        return $self;
    }

    /**
     * Convert DTO to array
     *
     * @since 2.20.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get donation type to display on front-end
     *
     * @since 2.21.0 refactor conditional for subscription renewals
     * @since 2.20.0
     * @param  object  $donation
     */
    private static function getDonationType($donation): string
    {
        /**
         * Initial donations will have a special meta key
         */
        if ($donation->{DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION()->getKeyAsCamelCase()}) {
            return 'subscription';
        }

        $hasRenewalStatus = $donation->status === DonationStatus::RENEWAL;
        $hasSubscriptionId = !empty($donation->{DonationMetaKeys::SUBSCRIPTION_ID()->getKeyAsCamelCase()});
        $hasRenewalMetaKey = !empty($donation->{DonationMetaKeys::IS_RECURRING()->getKeyAsCamelCase()});

        /**
         * Renewals are determined by a few different ways through GiveWP versions
         */
        if ($hasRenewalStatus || $hasSubscriptionId || $hasRenewalMetaKey) {
            return 'renewal';
        }

        return 'single';
    }
}
