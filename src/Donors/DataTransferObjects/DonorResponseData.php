<?php

namespace Give\Donors\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;
use Give\Helpers\Date;

/**
 * Class DonorResponseData
 *
 * @since 2.20.0
 */
class DonorResponseData implements Arrayable
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $titlePrefix;

    /**
     * @var int
     */
    public $donationCount;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string|null
     */
    public $donorType;
    /**
     * @var string
     */
    public $latestDonation;

    /**
     * @var string
     */
    public $donationRevenue;

    /**
     * @var string
     */
    public $gravatar;

    /**
     * Convert data from object to Donor
     *
     * @param object $donor
     *
     * @since 2.20.0
     *
     * @return self
     */
    public static function fromObject($donor)
    {
        $self = new static();

        $donorLatestDonationDate = give()->donors->getDonorLatestDonationDate($donor->id);

        $self->id = (int)$donor->id;
        $self->userId = (int)$donor->userId;
        $self->email = $donor->email;
        $self->name = $donor->name;
        $self->titlePrefix = $donor->titlePrefix;
        $self->donationCount = (int)$donor->donationCount;
        $self->dateCreated = Date::getDateTime($donor->createdAt);
        $self->donorType = give()->donors->getDonorType($donor->id);
        $self->latestDonation = $donorLatestDonationDate ? Date::getDateTime($donorLatestDonationDate) : '';
        $self->donationRevenue = $self->formatAmount($donor->donationRevenue);
        $self->gravatar = get_avatar_url($donor->email, ["size" => 64]);

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
     * @param string $amount
     * @since 2.20.0
     *
     * @return string
     */
    private function formatAmount($amount)
    {
        return html_entity_decode(give_currency_filter(give_format_amount($amount)));
    }
}
