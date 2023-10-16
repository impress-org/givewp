<?php

namespace Give\DonationForms\DataTransferObjects;

/**
 * @since 3.0.0
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
     * @since 3.0.0
     */
    public static function fromRequest(array $request): self
    {
        $self = new self();

        $self->login = $request['login'];
        $self->password = $request['password'];

        return $self;
    }
}
