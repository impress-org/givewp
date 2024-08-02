<?php

namespace Give\DonationSpam;

class EmailAddressWhiteList
{
    /**
     * @var array
     */
    protected $whitelistEmails;

    public function __construct($whitelistEmails = [])
    {
        $this->whitelistEmails = $whitelistEmails;
    }

    public function validate($email)
    {
        return in_array($email, $this->whitelistEmails, true);
    }
}
