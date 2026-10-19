<?php

namespace Give\Tests\Unit\Promotions\NexcessBanner;

use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Promotions\NexcessBanner\NexcessBannerAudience;
use Give\Tests\TestCase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\Tests\Unit\VendorOverrides\Harbor\HarborStubs;
use WP_Screen;

/**
 * @since 4.19.0
 */
class NexcessBannerAudienceTest extends TestCase
{
    use HasLicenseData;

    /**
     * @since 4.19.0
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
     * @since 4.19.0
     */
    public function tearDown(): void
    {
        HarborStubs::reset();
        set_current_screen('front');

        parent::tearDown();
    }

    /**
     * @since 4.19.0
     */
    public function testIncludesSitesWithOnlyALegacyLicense(): void
    {
        $this->givenALegacyLicense('valid');

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testIncludesSitesWithOnlyAConsolidatedLicense(): void
    {
        HarborStubs::$productActive = true;

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesSitesWithoutALicense(): void
    {
        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesSitesWhoseLegacyLicenseExpired(): void
    {
        $this->givenALegacyLicense('expired');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testIncludesTheSharedLicensingPage(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('settings_page_lw-software-manager');

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesOtherAdminPages(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('dashboard');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesTheFormEditor(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('give_forms');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesCreatingAForm(): void
    {
        $this->givenALegacyLicense('valid');
        $screen = WP_Screen::get('give_forms');
        $screen->action = 'add';
        set_current_screen($screen);

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testExcludesTheFormBuilder(): void
    {
        $this->givenALegacyLicense('valid');
        set_current_screen('admin_page_givewp-form-builder');

        $this->assertFalse($this->audience()->shouldDisplay());
    }

    /**
     * @since 4.19.0
     */
    public function testIncludesUsersWhoAreNotAdministrators(): void
    {
        $this->givenALegacyLicense('valid');
        wp_set_current_user(self::factory()->user->create(['role' => 'editor']));

        $this->assertTrue($this->audience()->shouldDisplay());
    }

    /**
     * Stores a legacy add-on license.
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
