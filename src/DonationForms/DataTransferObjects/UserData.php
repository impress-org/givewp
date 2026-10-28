<?php

namespace Give\DonationForms\DataTransferObjects;

use Give\Donors\Models\Donor;

/**
 * @since 3.0.0
 */
class UserData
{
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
     * Convert data from user into DTO
     *
     * @since TBD Return linked donor primary email if available.
     * @since 3.0.0
     */
    public static function fromUser(\WP_User $user): self
    {
        $self = new self();

        $self->firstName = $user->user_firstname;
        $self->lastName = $user->user_lastname;

        $donor = Donor::whereUserId($user->ID);
        $self->email = ($donor && !empty($donor->email)) ? $donor->email : $user->user_email;

        return $self;
    }
}
