<?php

namespace Give\Tests\Unit\DonationSpam;

use Give\DonationSpam\EmailAddressWhiteList;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
final class ServiceProviderTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testFilteredWhitelistIsArray()
    {
        add_filter('give_akismet_whitelist_emails', '__return_empty_string');

        give(EmailAddressWhiteList::class)
            ->validate('name@email.test');

        $this->assertTrue(true);
    }
}
