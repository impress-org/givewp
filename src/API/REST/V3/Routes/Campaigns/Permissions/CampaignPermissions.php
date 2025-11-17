<?php

namespace Give\API\REST\V3\Routes\Campaigns\Permissions;

use Give\Campaigns\ValueObjects\CampaignStatus;
use WP_Error;
use WP_REST_Request;

/**
 * @since 4.10.1
 */
class CampaignPermissions
{
    /**
     * Check if current user can edit campaigns.
     *
     * @since 4.10.1
     */
    public static function canEdit(): bool
    {
        return current_user_can('manage_options') ||
               current_user_can('edit_give_forms');
    }

    /**
     * Check if current user can view private/draft/archived campaigns.
     *
     * @since 4.10.1
     */
    public static function canViewPrivate(): bool
    {
        return current_user_can('manage_options') ||
               current_user_can('edit_give_forms');
    }

    /**
     * @since 4.10.1
     */
    public static function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * Validate campaign access permissions for GET items (collections).
     *
     * @since 4.10.1
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetItems(WP_REST_Request $request)
    {
        $status = $request->get_param('status');

        // If no status is specified, allow access (defaults to active campaigns)
        if (empty($status)) {
            return true;
        }

        // Convert single status to array for consistent handling
        if (!is_array($status)) {
            $status = [$status];
        }

        // Check if user is trying to access any non-active campaigns
        $hasNonActiveStatus = !empty(array_filter($status, function($campaignStatus) {
            return $campaignStatus !== CampaignStatus::ACTIVE;
        }));

        if ($hasNonActiveStatus && !self::canViewPrivate()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to view private, draft, or archived campaigns.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * Validate campaign access permissions for individual campaign GET method.
     *
     * @since 4.10.1
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetItem(WP_REST_Request $request)
    {
        // Individual campaign access validation is handled in the controller
        // where we can access the actual campaign data and its status
        return true;
    }
}
