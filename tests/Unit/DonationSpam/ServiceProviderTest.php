<?php

namespace Give\Tests\Unit\DonationSpam;

use Give\DonationSpam\EmailAddressWhiteList;
use Give\Tests\TestCase;

/**
 * @since 3.16.0
 */
final class ServiceProviderTest extends TestCase
{
    /**
     * @since 3.16.0
     */
    public function testFilteredWhitelistIsArray()
    {
        add_filter('give_akismet_whitelist_emails', '__return_empty_string');

        give(EmailAddressWhiteList::class)
            ->validate('name@email.test');

        $this->assertTrue(true);
    }
}
