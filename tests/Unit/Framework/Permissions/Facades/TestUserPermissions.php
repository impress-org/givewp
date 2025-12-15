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

    /**
     * @unreleased
     */
    public function testDonors(): void
    {
        $this->assertInstanceOf(
            DonorPermissions::class,
            UserPermissions::donors()
        );
    }

    /**
     * @unreleased
     */
    public function testDonations(): void
    {
        $this->assertInstanceOf(
            DonationPermissions::class,
            UserPermissions::donations()
        );
    }

    /**
     * @unreleased
     */
    public function testReports(): void
    {
        $this->assertInstanceOf(
            ReportsPermissions::class,
            UserPermissions::reports()
        );
    }

    /**
     * @unreleased
     */
    public function testSensitiveData(): void
    {
        $this->assertInstanceOf(
            SensitiveDataPermissions::class,
            UserPermissions::sensitiveData()
        );
    }

    /**
     * @unreleased
     */
    public function testSettings(): void
    {
        $this->assertInstanceOf(
            SettingsPermissions::class,
            UserPermissions::settings()
        );
    }
}
