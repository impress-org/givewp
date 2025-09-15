<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Permissions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use WP_Error;
use WP_REST_Request;

/**
 * @since 4.8.0
 */
class SubscriptionPermissions
{
    /**
     * Check if current user can edit subscriptions.
     *
     * @since 4.8.0
     */
    public static function canEdit(): bool
    {
        return current_user_can('manage_options')
               || (
                   current_user_can('edit_give_payments')
                   && current_user_can('view_give_payments')
               );
    }

    /**
     * Check if current user can delete subscriptions.
     *
     * @since 4.8.0
     */
    public static function canDelete(): bool
    {
        return current_user_can('manage_options') || current_user_can('delete_give_payments');
    }

    /**
     * @since 4.8.0
     */
    public static function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @since 4.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetMethods(WP_REST_Request $request)
    {
        $isAdmin = self::canEdit();

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if ( ! $isAdmin && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        if ($request->get_param('anonymousDonors') !== null) {
            $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
            if ( ! $isAdmin && $donorAnonymousMode->isIncluded()) {
                return new WP_Error(
                    'rest_forbidden',
                    esc_html__('You do not have permission to include anonymous donors.', 'give'),
                    ['status' => self::authorizationStatusCode()]
                );
            }
        }

        return true;
    }

    /**
     * @since 4.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForUpdateMethod(WP_REST_Request $request)
    {
        if (! self::canEdit()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to update subscriptions.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 4.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForDeleteMethods(WP_REST_Request $request)
    {
        if ( ! self::canDelete()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to delete subscriptions.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }
}
