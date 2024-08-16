<?php

namespace Give\Tests\Unit\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormsPermissions;
use Give\Framework\Permissions\Facades\Permissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDonationForms(): void
    {
        $this->assertInstanceOf(
            DonationFormsPermissions::class,
            Permissions::donationForms()
        );
    }

}
