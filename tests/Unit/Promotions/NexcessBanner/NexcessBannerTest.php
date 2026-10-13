<?php

namespace Give\Tests\Unit\Promotions\NexcessBanner;

use DateTimeImmutable;
use DateTimeZone;
use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Promotions\NexcessBanner\NexcessBanner;
use Give\Promotions\NexcessBanner\NexcessBannerAudience;
use Give\Tests\TestCase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;

// Loaded with this file, before any test renders a banner: see BannersClock.
require_once __DIR__ . '/BannersClock.php';

/**
 * @since TBD
 */
class NexcessBannerTest extends TestCase
{
    use HasLicenseData;

    /**
     * An administrator with an active add-on license on a GiveWP page, who should see the banner.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // Every copy of stellarwp/banners shares its registry through this global: start without the banner.
        unset($GLOBALS['nx_banners']);
        update_option(LicenseOptionKeys::LICENSES, ['license-key' => $this->getRawLicenseData()]);
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        set_current_screen('give_forms_page_give-payment-history');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        BannersClock::$now = null;
        unset($GLOBALS['nx_banners']);
        set_current_screen('front');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDoesNotShowBeforeItsStart(): void
    {
        $this->at('2026-10-14 11:59:59');

        $this->assertSame('', $this->renderBanner());
    }

    /**
     * @since TBD
     */
    public function testShowsFromItsStart(): void
    {
        $this->at('2026-10-14 12:00:00');

        $this->assertStringContainsString('notice nx-banner', $this->renderBanner());
    }

    /**
     * @since TBD
     */
    public function testKeepsShowingYearsLaterBecauseItHasNoEnd(): void
    {
        $this->at('2036-10-14 12:00:00');

        $this->assertStringContainsString('notice nx-banner', $this->renderBanner());
    }

    /**
     * Freezes the library's clock at a New York time.
     */
    private function at(string $time): void
    {
        BannersClock::$now = (new DateTimeImmutable($time, new DateTimeZone('America/New_York')))->getTimestamp();
    }

    /**
     * Registers the banner and renders it on the current screen.
     */
    private function renderBanner(): string
    {
        $banner = new NexcessBanner(new NexcessBannerAudience(new LicenseRepository()));
        $banner->register();

        ob_start();
        $banner->render();

        return (string)ob_get_clean();
    }
}
