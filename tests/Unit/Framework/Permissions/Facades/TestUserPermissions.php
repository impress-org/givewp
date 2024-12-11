<?php

namespace Give\Tests\Unit\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestUserPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDonationForms(): void
    {
        $this->assertInstanceOf(
            DonationFormPermissions::class,
            UserPermissions::donationForms()
        );
    }

}
