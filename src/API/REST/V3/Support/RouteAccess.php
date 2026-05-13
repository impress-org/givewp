<?php

namespace Give\API\REST\V3\Support;

use WP_REST_Request;

/**
 * Helper for determining whether a givewp/v3 REST API route may be accessed
 * without authentication.
 *
 * Routes that expose donor, donation, or subscription data are private by
 * default. Site owners or add-ons that need to expose a specific route to
 * unauthenticated requests can hook into the per-route filter
 * "givewp_rest_api_v3_{$route}_is_public" and return true.
 *
 * Example — make the donations collection publicly readable:
 *
 *     add_filter('givewp_rest_api_v3_donations_is_public', '__return_true');
 *
 * Or with the request for finer control:
 *
 *     add_filter('givewp_rest_api_v3_donor_notes_is_public', function ($isPublic, $request) {
 *         return $request->get_param('donorId') === '42' ? true : $isPublic;
 *     }, 10, 2);
 *
 * @since 4.15.2
 */
class RouteAccess
{
    const DONORS = 'donors';
    const DONATIONS = 'donations';
    const SUBSCRIPTIONS = 'subscriptions';
    const DONOR_NOTES = 'donor_notes';
    const DONATION_NOTES = 'donation_notes';
    const DONOR_STATISTICS = 'donor_statistics';

    /**
     * Whether the given route should be publicly accessible (no auth required).
     *
     * @since 4.15.2
     */
    public static function isPublic(string $route, WP_REST_Request $request): bool
    {
        /**
         * Filters whether a specific givewp/v3 REST API route should be
         * publicly accessible without authentication.
         *
         * Dynamic portion of the hook name, $route, refers to one of the
         * RouteAccess constants: donors, donations, subscriptions,
         * donor_notes, donation_notes, donor_statistics.
         *
         * Routes are private by default. Returning true makes the route
         * public for the current request.
         *
         * @since 4.15.2
         *
         * @param bool            $isPublic Whether the route is public. Default false.
         * @param WP_REST_Request $request  The current REST request.
         */
        return (bool) apply_filters("givewp_rest_api_v3_{$route}_is_public", false, $request);
    }
}
