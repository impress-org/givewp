<?php

namespace Give\Promotions\NexcessBanner;

use Give\License\Repositories\LicenseRepository;
use Give\Vendors\LiquidWeb\Harbor\Admin\Feature_Manager_Page;
use WP_Screen;

/**
 * Decides whether the Nexcess banner may show to the current user on the current screen.
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
     * Whether the banner may show to the current user on the current screen.
     *
     * @since TBD
     */
    public function shouldDisplay(): bool
    {
        // Plan changes concern whoever manages the site, not every donation manager.
        return current_user_can('manage_options')
            && $this->hasActivePlan()
            && ($this->isGiveWPPage() || $this->isLicenseManagerPage());
    }

    /**
     * Whether the site has a paid add-on with an active plan: a valid legacy add-on license or an
     * active consolidated Harbor license. Both are read from stored options, with no remote request.
     *
     * shortcut: a valid stored license counts even when its add-on is deactivated; checking the
     * add-on goes through give_get_plugins(), which can call givewp.com.
     *
     * @since TBD
     */
    private function hasActivePlan(): bool
    {
        return $this->licenses->hasActiveLicenses();
    }

    /**
     * Every GiveWP admin page sits under the give_forms post type's menu. Its pages keep the
     * give_forms_page_ screen ID even through the top-level GiveWP link, which drops the post type.
     * give_is_admin_page() is broader: it also matches the Dashboard and every post editor.
     *
     * @since TBD
     */
    private function isGiveWPPage(): bool
    {
        $screen = get_current_screen();

        // The visual form builder fills the window, so the banner would only sit hidden behind it.
        if (!$screen instanceof WP_Screen || $screen->id === 'admin_page_givewp-form-builder') {
            return false;
        }

        return $screen->post_type === 'give_forms' || strpos($screen->id, 'give_forms_page_') === 0;
    }

    /**
     * Whether this is the Nexcess licensing page, which every Nexcess plugin shares through Harbor.
     *
     * @since TBD
     */
    private function isLicenseManagerPage(): bool
    {
        $screen = get_current_screen();

        return $screen instanceof WP_Screen && $screen->id === 'settings_page_' . Feature_Manager_Page::PAGE_SLUG;
    }
}
