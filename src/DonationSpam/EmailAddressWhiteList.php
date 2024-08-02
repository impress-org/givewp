<?php

namespace Give\DonationSpam;

/**
 * @unreleased
 */
class EmailAddressWhiteList
{
    /**
     * @var array
     */
    protected $whitelistEmails;

    /**
     * @unreleased
     */
    public function __construct($whitelistEmails = [])
    {
        $this->whitelistEmails = $whitelistEmails;
    }

    /**
     * @unreleased
     */
    public function validate($email): bool
    {
        return in_array($email, $this->whitelistEmails, true);
    }
}
