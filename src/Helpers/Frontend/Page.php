<?php

namespace Give\Helpers\Frontend;

/**
 * Frontend Page Detection Helper
 *
 * Provides utilities for detecting Give content on frontend pages
 *
 * @unreleased
 */
class Page
{
    /**
     * Check if current page has Give content (forms, blocks, or is a Give page)
     *
     * This method checks for:
     * - Give form post types
     * - Give success/campaign pages
     * - Give shortcodes in content
     * - Give blocks in content
     *
     * @unreleased
     */
    public static function hasGiveContent(): bool
    {
        global $post;

        if (self::isGiveEmbed()) {
            return true;
        }

        if (!is_single() && !is_page()) {
            return false;
        }

        // Check if it's a Give success page
        if (give_is_success_page()) {
            return true;
        }

        // Check if it's a campaign page
        if (function_exists('give_is_campaign_page') && give_is_campaign_page()) {
            return true;
        }

        // Check if it's a single give_forms post type
        if (is_singular('give_forms')) {
            return true;
        }

        // No post to check
        if (!$post instanceof \WP_Post) {
            return apply_filters('give_page_has_give_content', false);
        }

        // Check for Give shortcodes
        if (self::hasGiveShortcode($post->post_content)) {
            return true;
        }

        // Check for Give blocks
        if (self::hasGiveBlock($post->post_content)) {
            return true;
        }


        // Allow filtering for custom conditions
        return apply_filters('give_page_has_give_content', false);
    }

    /**
     * Check if content has any Give shortcode
     *
     * @unreleased
     */
    public static function hasGiveShortcode(string $content): bool
    {
        $shortcodes = [
            'donation_history',
            'give_form',
            'give_goal',
            'give_login',
            'give_register',
            'give_receipt',
            'give_profile_editor',
            'give_totals',
            'give_form_grid',
            'give_donor_wall',
            'givewp_campaign',
            'givewp_campaign_grid',
            'givewp_campaign_form',
            'givewp_campaign_comments',
            'givewp_campaign_donors',
            'givewp_campaign_donations',
            'givewp_campaign_stats',
            'givewp_campaign_goal',
            'give_donor_dashboard',
            'give_multi_form_goal',
        ];

        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($content, $shortcode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content has any Give block
     *
     * @unreleased
     */
    public static function hasGiveBlock(string $content): bool
    {
        if (!function_exists('has_block')) {
            return false;
        }

        $blocks = [
            // Core donation form blocks
            'give/donation-form',
            'give/donation-form-grid',
            'givewp/donation-form',

            // Donor blocks
            'give/donor-dashboard',
            'give/donor-wall',

            // Multi-form goal blocks
            'give/multi-form-goal',
            'give/progress-bar',

            // Campaign blocks
            'givewp/campaign-block',
            'givewp/campaign-grid',
            'givewp/campaign-form',
            'givewp/campaign-goal',
            'givewp/campaign-stats-block',
            'givewp/campaign-comments-block',
            'givewp/campaign-donors',
            'givewp/campaign-donations',
            'givewp/campaign-cover-block',
            'givewp/campaign-title',
            'givewp/campaign-donate-button',

            // Event tickets (add-on)
            'givewp/event-tickets',
        ];

        foreach ($blocks as $block) {
            if (has_block($block)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Check if current page is a Give embed
     *
     * @unreleased
     */
    public static function isGiveEmbed(): bool
    {
        $keys = [
            'give-embed',
            'givewp-route',
            'giveDonationFormInIframe',
        ];

        foreach ($keys as $key) {
            if (filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING) !== null) {
                return true;
            }
        }

        return false;
    }
}
