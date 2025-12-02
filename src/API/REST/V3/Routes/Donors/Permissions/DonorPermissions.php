<?php

namespace Give\API\REST\V3\Routes\Donors\Permissions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use WP_Error;
use WP_REST_Request;

/**
 * @unreleased
 */
class DonorPermissions
{
    /**
     * Check if current user can edit donors.
     *
     * @unreleased
     */
    public static function canEdit(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * @unreleased
     */
    public static function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetMethods(WP_REST_Request $request)
    {
        $isAdmin = self::canEdit();

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if (!$isAdmin && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        if ($request->get_param('anonymousDonors') !== null) {
            $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
            if (!$isAdmin && $donorAnonymousMode->isIncluded()) {
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
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForUpdateMethod(WP_REST_Request $request)
    {
        if (!self::canEdit()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to update donors.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }
}
