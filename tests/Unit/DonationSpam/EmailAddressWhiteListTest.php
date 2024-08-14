<?php

namespace Give\Tests\Unit\DonationSpam;

use Give\DonationSpam\EmailAddressWhiteList;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
final class EmailAddressWhiteListTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testValidatesWhitelistedEmailAddress()
    {
        $validator = new EmailAddressWhiteList(['admin@wordpress.test']);
        $this->assertTrue($validator->validate('admin@wordpress.test'));
    }

    /**
     * @unreleased
     */
    public function testDoesNotValidateNonWhitelistedEmailAddress()
    {
        $validator = new EmailAddressWhiteList(['admin@wordpress.test']);
        $this->assertFalse($validator->validate('subscriber@wordpress.test'));
    }
}
