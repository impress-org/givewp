<?php

namespace Give\API\REST\V3\Routes\Donors\Permissions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\Models\Donor;
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
     * Check if current user owns the donor (is the donor themselves).
     *
     * @unreleased
     *
     * @unreleased
     */
    public static function isOwner(int $donorId): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $donor = Donor::find($donorId);
        if (!$donor || !$donor->userId) {
            return false;
        }

        return (int)$donor->userId === get_current_user_id();
    }

    /**
     * @unreleased
     */
    public static function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @unreleased Allow donors to view their own data
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetMethods(WP_REST_Request $request)
    {
        $isAdmin = self::canEdit();
        $donorId = $request->get_param('id');
        $isOwner = $donorId ? self::isOwner($donorId) : false;

        // Allow access if user is admin or owns the donor
        if (!$isAdmin && !$isOwner) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to view this donor.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if (!$isAdmin && !$isOwner && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        if ($request->get_param('anonymousDonors') !== null) {
            $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
            if (!$isAdmin && !$isOwner && $donorAnonymousMode->isIncluded()) {
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
     * @unreleased Allow donors to update their own data
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForUpdateMethod(WP_REST_Request $request)
    {
        $isAdmin = self::canEdit();
        $donorId = $request->get_param('id');
        $isOwner = $donorId ? self::isOwner($donorId) : false;

        // Allow access if user is admin or owns the donor
        if (!$isAdmin && !$isOwner) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to update this donor.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }
}
