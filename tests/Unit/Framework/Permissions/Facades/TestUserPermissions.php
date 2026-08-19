<?php

namespace Give\Tests\Unit\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\DonationPermissions;
use Give\Framework\Permissions\DonorPermissions;
use Give\Framework\Permissions\ReportsPermissions;
use Give\Framework\Permissions\SensitiveDataPermissions;
use Give\Framework\Permissions\SettingsPermissions;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.14.0
 */
final class TestUserPermissions extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.14.0
     */
    public function testDonationForms(): void
    {
        $this->assertInstanceOf(
            DonationFormPermissions::class,
            UserPermissions::donationForms()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testDonors(): void
    {
        $this->assertInstanceOf(
            DonorPermissions::class,
            UserPermissions::donors()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testDonations(): void
    {
        $this->assertInstanceOf(
            DonationPermissions::class,
            UserPermissions::donations()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testReports(): void
    {
        $this->assertInstanceOf(
            ReportsPermissions::class,
            UserPermissions::reports()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testSensitiveData(): void
    {
        $this->assertInstanceOf(
            SensitiveDataPermissions::class,
            UserPermissions::sensitiveData()
        );
    }

    /**
     * @since 4.14.0
     */
    public function testSettings(): void
    {
        $this->assertInstanceOf(
            SettingsPermissions::class,
            UserPermissions::settings()
        );
    }
}
