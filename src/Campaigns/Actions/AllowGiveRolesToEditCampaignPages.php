<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use WP_User;

/**
 * Allow users with Give roles to edit and publish campaign landing pages.
 *
 * Campaign pages are standard WordPress pages (post_type = 'page') that have
 * the give_campaign_id meta key. Give roles like give_worker and give_manager
 * have edit_pages but not edit_others_pages or publish_pages, so this action
 * maps the meta capabilities to allow full management of campaign pages
 * for users with edit_give_forms capability.
 *
 * @since 4.14.0
 */
class AllowGiveRolesToEditCampaignPages
{
    /**
     * Cache for campaign page checks to avoid repeated DB queries.
     *
     * @var array<int, bool>
     */
    private static array $campaignPageCache = [];

    /**
     * Filter meta capabilities for campaign pages.
     *
     * Hooked to 'map_meta_cap' filter.
     *
     * @since 4.14.0
     */
    public function mapMetaCap(array $caps, string $cap, int $userId, array $args): array
    {
        // Fast check: only handle specific meta capabilities
        static $pageMetaCaps = ['edit_post' => true, 'delete_post' => true, 'publish_post' => true, 'read_post' => true];
        if (!isset($pageMetaCaps[$cap])) {
            return $caps;
        }

        // We need a post ID to check
        if (empty($args[0])) {
            return $caps;
        }

        // Check if user has Give capability first (cheaper than DB lookups)
        if (!user_can($userId, 'edit_give_forms')) {
            return $caps;
        }

        // Check if this is a campaign page (uses cache)
        if (!$this->isCampaignPage((int)$args[0])) {
            return $caps;
        }

        // Grant full access to campaign pages
        return [];
    }

    /**
     * Dynamically grant page capabilities when working with a campaign page.
     *
     * The block editor and REST API check primitive capabilities like 'publish_pages'
     * directly (not through map_meta_cap), so we need to dynamically add them.
     *
     * Hooked to 'user_has_cap' filter.
     *
     * @since 4.14.0
     */
    public function grantPublishCapability(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        // Fast check: skip if not in admin or REST context
        if (!is_admin() && !wp_is_serving_rest_request()) {
            return $allcaps;
        }

        // Fast check: only process if specific page caps are requested
        static $pageCaps = ['publish_pages' => true, 'edit_others_pages' => true, 'edit_published_pages' => true, 'delete_others_pages' => true];
        $requestedPageCaps = array_filter($caps, static function ($cap) use ($pageCaps) {
            return is_string($cap) && isset($pageCaps[$cap]);
        });
        if (empty($requestedPageCaps)) {
            return $allcaps;
        }

        // User must have edit_give_forms capability (check from allcaps, no DB query)
        if (empty($allcaps['edit_give_forms'])) {
            return $allcaps;
        }

        // Get the post being edited (cached)
        $postId = $this->getCurrentEditingPostId();
        if (!$postId) {
            return $allcaps;
        }

        // Check if this is a campaign page (uses cache)
        if (!$this->isCampaignPage($postId)) {
            return $allcaps;
        }

        // Grant the requested page capabilities
        foreach ($requestedPageCaps as $cap) {
            $allcaps[$cap] = true;
        }

        return $allcaps;
    }

    /**
     * Check if a post is a campaign page (with caching).
     *
     * @since 4.14.0
     */
    private function isCampaignPage(int $postId): bool
    {
        if (isset(self::$campaignPageCache[$postId])) {
            return self::$campaignPageCache[$postId];
        }

        $post = get_post($postId);
        if (!$post || $post->post_type !== 'page') {
            self::$campaignPageCache[$postId] = false;
            return false;
        }

        $campaignId = get_post_meta($postId, CampaignPageMetaKeys::CAMPAIGN_ID, true);
        self::$campaignPageCache[$postId] = !empty($campaignId);

        return self::$campaignPageCache[$postId];
    }

    /**
     * Get the post ID currently being edited (with static caching).
     *
     * @since 4.14.0
     */
    private function getCurrentEditingPostId(): ?int
    {
        static $cachedPostId = null;
        static $checked = false;

        if ($checked) {
            return $cachedPostId;
        }
        $checked = true;

        // Check for post ID in query string (standard edit screen)
        if (!empty($_GET['post'])) {
            $cachedPostId = (int)$_GET['post'];
            return $cachedPostId;
        }

        // Check REST API for post ID (query param or route)
        if (wp_is_serving_rest_request()) {
            if (!empty($_GET['post_id'])) {
                $cachedPostId = (int)$_GET['post_id'];
                return $cachedPostId;
            }

            $cachedPostId = $this->getPostIdFromRestRoute();
            if ($cachedPostId) {
                return $cachedPostId;
            }
        }

        // Check global $post
        global $post;
        if ($post instanceof \WP_Post) {
            $cachedPostId = $post->ID;
            return $cachedPostId;
        }

        return null;
    }

    /**
     * Extract post ID from REST API route.
     *
     * @since 4.14.0
     */
    private function getPostIdFromRestRoute(): ?int
    {
        static $cachedRestPostId = null;
        static $restChecked = false;

        if ($restChecked) {
            return $cachedRestPostId;
        }
        $restChecked = true;

        global $wp;
        $restRoute = $wp->query_vars['rest_route'] ?? '';

        // Match routes like /wp/v2/pages/123
        if (preg_match('#/wp/v2/pages/(\d+)#', $restRoute, $matches)) {
            $cachedRestPostId = (int)$matches[1];
        }

        return $cachedRestPostId;
    }
}

