<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @unreleased
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
     * @unreleased
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
