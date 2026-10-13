<?php

namespace Give\Tests\Unit\Promotions\NexcessBanner;

use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Promotions\NexcessBanner\NexcessBannerAudience;
use Give\Tests\TestCase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\Tests\Unit\VendorOverrides\Harbor\HarborStubs;

/**
 * @since TBD
 */
class NexcessBannerAudienceTest extends TestCase
{
    use HasLicenseData;

    /**
     * An administrator on a GiveWP page, with no license until a test adds one.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        HarborStubs::reset();
        delete_option(LicenseOptionKeys::LICENSES);
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        set_current_screen('give_forms_page_give-payment-history');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        HarborStubs::reset();
        set_current_screen('front');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testIncludesSitesWithOnlyALegacyLicense(): void
    {
        $this->givenALegacyLicense('valid');

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testIncludesSitesWithOnlyAConsolidatedLicense(): void
    {
        HarborStubs::$productActive = true;

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testExcludesSitesWithoutALicense(): void
    {
        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testExcludesSitesWhoseLegacyLicenseExpired(): void
    {
        $this->givenALegacyLicense('expired');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testIncludesTheSharedLicensingPage(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('settings_page_lw-software-manager');

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testExcludesOtherAdminPages(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('dashboard');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testExcludesTheFormBuilder(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('admin_page_givewp-form-builder');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testExcludesUsersWhoCannotManageOptions(): void
    {
        $this->givenALegacyLicense('valid');
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * Stores a legacy add-on license as GiveWP records it after checking it.
     */
    private function givenALegacyLicense(string $status): void
    {
        update_option(LicenseOptionKeys::LICENSES, ['license-key' => $this->getRawLicenseData(['license' => $status])]);
    }

    private function audience(): NexcessBannerAudience
    {
        return new NexcessBannerAudience(new LicenseRepository());
    }
}
