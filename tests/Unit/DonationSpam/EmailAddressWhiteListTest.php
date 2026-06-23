<?php

namespace Give\Tests\Unit\DonationSpam;

use Give\DonationSpam\EmailAddressWhiteList;
use Give\Tests\TestCase;

/**
 * @since 3.15.0
 */
final class EmailAddressWhiteListTest extends TestCase
{
    /**
     * @since 3.15.0
     */
    public function testValidatesWhitelistedEmailAddress()
    {
        $validator = new EmailAddressWhiteList(['admin@wordpress.test']);
        $this->assertTrue($validator->validate('admin@wordpress.test'));
    }

    /**
     * @since 3.15.0
     */
    public function testDoesNotValidateNonWhitelistedEmailAddress()
    {
        $validator = new EmailAddressWhiteList(['admin@wordpress.test']);
        $this->assertFalse($validator->validate('subscriber@wordpress.test'));
    }

    /**
     * @since 4.16.0
     */
    public function testValidatesWhitelistedEmailAddressIgnoringCaseAndWhitespace()
    {
        $validator = new EmailAddressWhiteList([' Admin@WordPress.test ']);
        $this->assertTrue($validator->validate('admin@wordpress.test'));
        $this->assertTrue($validator->validate('  ADMIN@wordpress.TEST'));
    }
}
