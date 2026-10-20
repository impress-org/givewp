<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\Framework\Database\DB;
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
     * Resolved post ID for the current request.
     *
     * @var int|null
     */
    private static ?int $editingPostId = null;

    /**
     * Whether the current request's post ID has already been resolved.
     *
     * @var bool
     */
    private static bool $editingPostIdResolved = false;

    /**
     * Filter meta capabilities for campaign pages.
     *
     * Hooked to 'map_meta_cap' filter.
     *
     * @since 4.15.3 Handle null $cap gracefully for better compatibility.
     * @since 4.14.0
     */
    public function mapMetaCap(array $caps, ?string $cap, int $userId, array $args): array
    {
        // Fast check: only handle specific meta capabilities
        static $pageMetaCaps = ['edit_post' => true, 'delete_post' => true, 'publish_post' => true, 'read_post' => true];
        if (!is_string($cap) || !isset($pageMetaCaps[$cap])) {
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
     * @since TBD Grant page capabilities based on the page actually being edited.
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
     * @since 4.16.7 Read the campaign ID meta directly instead of through get_post_meta().
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

        /*
         * get_post_meta() fires the get_post_metadata filter, which third parties hook to run
         * capability checks. Those re-enter this action through map_meta_cap and recurse until
         * the call stack is exhausted, so read the meta without going through the filter.
         */
        $campaignPageMeta = DB::table('postmeta')
            ->select('meta_value')
            ->where('post_id', $postId)
            ->where('meta_key', CampaignPageMetaKeys::CAMPAIGN_ID)
            ->get();

        self::$campaignPageCache[$postId] = !empty($campaignPageMeta->meta_value);

        return self::$campaignPageCache[$postId];
    }

    /**
     * Get the post ID currently being edited.
     *
     * The ID is resolved from the REST route or the global $post, matching the
     * page the current request or screen is actually operating on. Only a
     * resolved ID is cached, so the grant is not locked out when an early
     * capability check runs before the edit context exists.
     *
     * @since TBD Resolve the edited page from the request context instead of the query string, caching only resolved IDs.
     * @since 4.14.0
     */
    private function getCurrentEditingPostId(): ?int
    {
        if (self::$editingPostIdResolved) {
            return self::$editingPostId;
        }

        // Check REST API route for the post ID
        if (wp_is_serving_rest_request()) {
            self::$editingPostId = $this->getPostIdFromRestRoute();
            if (self::$editingPostId) {
                self::$editingPostIdResolved = true;
                return self::$editingPostId;
            }
        }

        // Check global $post
        global $post;
        if ($post instanceof \WP_Post) {
            self::$editingPostId = $post->ID;
            self::$editingPostIdResolved = true;
            return self::$editingPostId;
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

