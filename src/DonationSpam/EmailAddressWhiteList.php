<?php

namespace Give\DonationSpam;

/**
 * @since 3.15.0
 */
class EmailAddressWhiteList
{
    /**
     * @var array
     */
    protected $whitelistEmails;

    /**
     * @since 3.15.0
     */
    public function __construct($whitelistEmails = [])
    {
        $this->whitelistEmails = $whitelistEmails;
    }

    /**
     * @since 3.15.0
     */
    public function validate($email): bool
    {
        return in_array($email, $this->whitelistEmails, true);
    }
}
