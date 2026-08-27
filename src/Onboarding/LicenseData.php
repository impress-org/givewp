<?php

namespace Give\Onboarding;

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
        return function_exists('lw_harbor_get_product_activation_url');
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

        if (!$this->needsActivation()) {
            return '';
        }

        return $this->buildActivationUrl($returnUrl);
    }

    /**
     * Build the activation URL whatever this site's activation state. Harbor
     * scopes the URL to the tier the license covers GiveWP at, so the portal
     * pre-selects the right subscription. Where it cannot — the key does not
     * cover GiveWP, or covers it at several tiers — the portal shows its own
     * picker, still limited to this domain. That is the right screen for a
     * genuine choice, and better than guessing on the user's behalf.
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
        // can see the function is called only when it exists.
        if (!function_exists('lw_harbor_get_product_activation_url')) {
            return '';
        }

        return lw_harbor_get_product_activation_url(self::PRODUCT_SLUG, $returnUrl) ?? '';
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
     * Whether the key entitles this site to GiveWP without having activated it
     * here yet — the one state an activation prompt is for.
     *
     * Harbor pairs the entitlement and activation checks so consumers do not
     * each rewrite the conditional. Asking only whether the license is active
     * would offer activation to someone with no entitlement, who would then
     * reach a portal with nothing for them.
     *
     * @since TBD
     *
     * @return bool True when GiveWP is entitled but not activated here.
     */
    public function needsActivation(): bool
    {
        return function_exists('lw_harbor_product_needs_activation')
            && lw_harbor_product_needs_activation(self::PRODUCT_SLUG);
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
        return function_exists('lw_harbor_is_product_license_active')
            && lw_harbor_is_product_license_active(self::PRODUCT_SLUG);
    }
}
