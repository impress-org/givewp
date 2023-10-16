<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.0.0
 *
 * @method static RegistrationOptions NONE()
 * @method static RegistrationOptions REGISTRATION()
 * @method static RegistrationOptions LOGIN()
 * @method static RegistrationOptions REGISTRATION_LOGIN()
 * @method bool isNone()
 * @method bool isRegistration()
 * @method bool isLogin()
 * @method bool isRegisterAndLogin()
 */
class RegistrationOptions extends Enum
{
    const NONE = 'none';
    const REGISTRATION = 'registration';
    const LOGIN = 'login';
    const REGISTRATION_LOGIN = 'register_and_login';
}
