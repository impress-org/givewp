<?php

namespace Give\API\REST\V3\Support;

use WP_REST_Request;

/**
 * Helper for determining whether a givewp/v3 REST API route should require
 * authentication.
 *
 * Routes that expose donor, donation, or subscription data are public by
 * default. Site owners or add-ons that need to restrict a specific route to
 * authenticated users can hook into the per-route filter
 * "givewp_rest_api_v3_{$route}_is_private" and return true.
 *
 * Example — restrict the donations collection to authenticated users:
 *
 *     add_filter('givewp_rest_api_v3_donations_is_private', '__return_true');
 *
 * Or with the request for finer control:
 *
 *     add_filter('givewp_rest_api_v3_donor_notes_is_private', function ($isPrivate, $request) {
 *         return $request->get_param('donorId') === '42' ? true : $isPrivate;
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
     * Whether the given route should require authentication.
     *
     * @since 4.15.2
     */
    public static function isPrivate(string $route, WP_REST_Request $request): bool
    {
        /**
         * Filters whether a specific givewp/v3 REST API route should require
         * authentication.
         *
         * Dynamic portion of the hook name, $route, refers to one of the
         * RouteAccess constants: donors, donations, subscriptions,
         * donor_notes, donation_notes, donor_statistics.
         *
         * Routes are public by default. Returning true restricts the route
         * to authenticated users for the current request.
         *
         * @since 4.15.2
         *
         * @param bool            $isPrivate Whether the route is private. Default false.
         * @param WP_REST_Request $request   The current REST request.
         */
        return (bool) apply_filters("givewp_rest_api_v3_{$route}_is_private", false, $request);
    }
}
