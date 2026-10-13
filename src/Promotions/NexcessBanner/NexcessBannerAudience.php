<?php

namespace Give\Promotions\NexcessBanner;

use Give\License\Repositories\LicenseRepository;
use Give\Vendors\LiquidWeb\Harbor\Admin\Feature_Manager_Page;
use WP_Screen;

/**
 * Decides whether the Nexcess banner may show on the current screen.
 *
 * @since TBD
 */
class NexcessBannerAudience
{
    /**
     * @var LicenseRepository
     */
    private $licenses;

    /**
     * @since TBD
     */
    public function __construct(LicenseRepository $licenses)
    {
        $this->licenses = $licenses;
    }

    /**
     * Whether the banner may show on the current screen.
     *
     * @since TBD
     */
    public function shouldDisplay(): bool
    {
        return $this->hasActivePlan()
            && ($this->isGiveWPPage() || $this->isLicenseManagerPage());
    }

    /**
     * Whether a legacy add-on license or a consolidated license is active, from stored options only.
     *
     * shortcut: a valid stored license counts even when its add-on is deactivated.
     *
     * @since TBD
     */
    private function hasActivePlan(): bool
    {
        return $this->licenses->hasActiveLicenses();
    }

    /**
     * Whether this is one of GiveWP's admin pages, apart from the visual form builder.
     *
     * @since TBD
     */
    private function isGiveWPPage(): bool
    {
        $screen = get_current_screen();

        if (!$screen instanceof WP_Screen || $screen->id === 'admin_page_givewp-form-builder') {
            return false;
        }

        return $screen->post_type === 'give_forms' || strpos($screen->id, 'give_forms_page_') === 0;
    }

    /**
     * Whether this is the Nexcess licensing page.
     *
     * @since TBD
     */
    private function isLicenseManagerPage(): bool
    {
        $screen = get_current_screen();

        return $screen instanceof WP_Screen && $screen->id === 'settings_page_' . Feature_Manager_Page::PAGE_SLUG;
    }
}
