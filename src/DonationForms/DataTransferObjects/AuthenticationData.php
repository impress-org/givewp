<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @since 0.1.0
 */
class AuthenticationData
{
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $password;

    /**
     * Convert data from request into DTO
     *
     * @since 0.1.0
     */
    public static function fromRequest(array $request): AuthenticationData
    {
        $self = new static();

        $self->login = $request['login'];
        $self->password = $request['password'];

        return $self;
    }
}
