<?php

namespace Give\Onboarding;

use Give\Vendors\LiquidWeb\Harbor\Licensing\Product_Collection;
use Give\Vendors\LiquidWeb\Harbor\Licensing\Repositories\License_Repository;
use Give\Vendors\LiquidWeb\Harbor\Licensing\Results\Product_Entry;

/**
 * Answers the licensing questions GiveWP's onboarding UI asks: whether this site
 * has activated GiveWP, and where to send a user who still has something to do
 * about it. Keeps the onboarding screens about rendering rather than about Harbor.
 *
 * The plugin reaches Harbor through its stable lw_harbor_* global functions rather
 * than resolving Harbor's classes directly: the functions always resolve to the
 * loaded, highest-version Harbor copy, so onboarding is never tied to whichever
 * version this plugin happens to bundle. Every method degrades to "nothing to
 * offer" — false, null or an empty string — when the loaded Harbor is older than
 * the API it needs, so callers can treat that as "hide the UI".
 *
 * @since TBD
 */
class LicenseData
{
    /**
     * The product slug GiveWP is licensed under in the Liquid Web catalog.
     *
     * @since TBD
     */
    private const PRODUCT_SLUG = 'give';

    /**
     * Whether the loaded Harbor can build an activation URL at all. Older copies
     * predate the activation URL API; callers guard on this so they can skip work
     * they would only discard, or hide UI they cannot drive.
     *
     * @since TBD
     */
    public function canBuildActivationUrl(): bool
    {
        return function_exists('lw_harbor_get_activation_base_url')
            && function_exists('lw_harbor_get_product_activation_url');
    }

    /**
     * Whether this site runs any active GiveWP premium add-on.
     *
     * GiveWP is free, and a license only unlocks premium add-ons and their updates.
     * A site running none of them has nothing to activate, so putting activation UI
     * in front of that user would imply a license is needed to fundraise at all.
     * Onboarding asks this first and stays silent when the answer is no.
     *
     * @since TBD
     */
    public function hasActivePremiumAddons(): bool
    {
        // give_get_plugins() leans on get_plugins(), which only ships with the admin.
        if (!function_exists('get_plugins')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach (give_get_plugins(['only_premium_add_ons' => true]) as $addon) {
            if ($addon['Status'] === 'active') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the URL that sends a user to the portal to activate a license, for
     * callers that only want one while the user still has something to do.
     *
     * Empty when there is nothing to be done: this site runs no premium add-on a
     * license would unlock, the loaded Harbor predates the API, or the site already
     * holds a valid activated license.
     *
     * @since TBD
     */
    public function getActivationUrl(string $returnUrl): string
    {
        if (!$this->hasActivePremiumAddons()) {
            return '';
        }

        if (!$this->canBuildActivationUrl()) {
            return '';
        }

        if ($this->isActivated()) {
            return '';
        }

        return $this->buildActivationUrl($returnUrl);
    }

    /**
     * Build the activation URL whatever this site's activation state. When the
     * stored license already covers GiveWP the URL is scoped to the product and
     * tier so the portal pre-selects the right subscription.
     *
     * Harbor returns null when it has no URL to give; that is folded into the
     * empty string this returns, because both mean the same thing here — there
     * is nothing to link to.
     *
     * @since TBD
     */
    public function buildActivationUrl(string $returnUrl): string
    {
        // Guarded inline (not only via canBuildActivationUrl()) so static analysis
        // can see the functions are called only when they exist.
        if (
            !function_exists('lw_harbor_get_activation_base_url')
            || !function_exists('lw_harbor_get_product_activation_url')
        ) {
            return '';
        }

        $entitlement = $this->getLicensedEntry();

        if (!$entitlement instanceof Product_Entry) {
            return lw_harbor_get_activation_base_url($returnUrl) ?? '';
        }

        return lw_harbor_get_product_activation_url(
            $entitlement->get_product_slug(),
            $entitlement->get_tier(),
            $returnUrl
        ) ?? '';
    }

    /**
     * Get the URL of the in-WP page where a user manages their Liquid Web licenses.
     * This is the Software Manager settings page Harbor registers, not the external
     * portal: an activated user manages their products without leaving the site.
     *
     * @since TBD
     */
    public function getManagementUrl(): string
    {
        if (!function_exists('lw_harbor_get_license_page_url')) {
            return '';
        }

        return lw_harbor_get_license_page_url();
    }

    /**
     * Whether this site already holds a valid, activated license for GiveWP.
     * Mirrors Harbor's own license UI: an entry counts only when it is activated
     * against this domain and its entitlement is currently valid.
     *
     * @since TBD
     */
    public function isActivated(): bool
    {
        $products = $this->getProducts();

        if (!$products instanceof Product_Collection) {
            return false;
        }

        $entry = $products->get_activated_entry(self::PRODUCT_SLUG);

        return $entry instanceof Product_Entry && $entry->is_valid();
    }

    /**
     * Get the licensed entry for GiveWP, whatever its activation state. Used to
     * scope the activation URL: the tier is known as soon as the key covers the
     * product, well before activation, so this deliberately does not filter on it.
     *
     * @since TBD
     */
    protected function getLicensedEntry(): ?Product_Entry
    {
        $products = $this->getProducts();

        if (!$products instanceof Product_Collection) {
            return null;
        }

        $entries = $products->get_all_by_slug(self::PRODUCT_SLUG);

        return $entries ? reset($entries) : null;
    }

    /**
     * Get the licensed products Harbor holds for this site. Harbor returns a
     * WP_Error when its last fetch failed and null when it has never fetched; both
     * are flattened to null here and read by callers as "no license".
     *
     * @since TBD
     */
    protected function getProducts(): ?Product_Collection
    {
        if (!class_exists(License_Repository::class)) {
            return null;
        }

        $products = give(License_Repository::class)->get_products();

        return $products instanceof Product_Collection ? $products : null;
    }
}
