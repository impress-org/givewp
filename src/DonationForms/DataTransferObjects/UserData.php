<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @since 0.4.0
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
     * @since 0.4.0
     */
    public static function fromUser(\WP_User $user): UserData
    {
        $self = new static();

        $self->firstName = $user->user_firstname;
        $self->lastName = $user->user_lastname;
        $self->email = $user->user_email;

        return $self;
    }
}
