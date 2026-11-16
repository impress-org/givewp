<?php

namespace Give\API\REST\V3\Routes\Donors\Permissions;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\Models\Donor;
use Give\Framework\Permissions\Facades\UserPermissions;
use WP_Error;
use WP_REST_Request;

/**
 * @since 4.14.0
 */
class DonorPermissions
{
    /**
     * Check if current user can edit donors.
     *
     * @since 4.14.0
     */
    public static function canEdit(): bool
    {
        return UserPermissions::donors()->canEdit();
    }


    /**
     * Check if current user can view donors.
     *
     * @since 4.14.0
     */
    public static function canView(): bool
    {
        return UserPermissions::donors()->canView();
    }

    /**
     * Check if current user owns the donor (is the donor themselves).
     *
     * @since 4.14.0
     *
     * @since 4.14.0
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
     * @since 4.14.0
     */
    public static function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * Validates a request for a single donor. The owner of the donor may read their own sensitive data.
     *
     * @since TBD Move the shared checks to validateReadAccess().
     * @since 4.14.0
     *
     * @param WP_REST_Request $request The request for a single donor.
     *
     * @return true|WP_Error True when the request is allowed, otherwise the error to return.
     */
    public static function validationForGetMethods(WP_REST_Request $request)
    {
        $donorId = $request->get_param('id');
        $isOwner = $donorId ? self::isOwner($donorId) : false;

        return self::validateReadAccess($request, $isOwner);
    }

    /**
     * Validates a request for the donors collection. Owning a donor grants no access here, because the
     * collection returns other donors.
     *
     * @since TBD
     *
     * @param WP_REST_Request $request The request for the donors collection.
     *
     * @return true|WP_Error True when the request is allowed, otherwise the error to return.
     */
    public static function validationForGetCollectionMethod(WP_REST_Request $request)
    {
        return self::validateReadAccess($request, false);
    }

    /**
     * @since 4.14.0 Allow donors to update their own data
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
                __('You do not have permission to update this donor.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since TBD
     *
     * @param WP_REST_Request $request The read request.
     * @param bool            $isOwner Whether the current user owns the requested donor.
     *
     * @return true|WP_Error True when the request is allowed, otherwise the error to return.
     */
    private static function validateReadAccess(WP_REST_Request $request, bool $isOwner)
    {
        $isAdmin = self::canView();

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if (!$isAdmin && !$isOwner && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to include sensitive data.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        if ($request->get_param('anonymousDonors') !== null) {
            $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
            if (!$isAdmin && !$isOwner && $donorAnonymousMode->isIncluded()) {
                return new WP_Error(
                    'rest_forbidden',
                    __('You do not have permission to include anonymous donors.', 'give'),
                    ['status' => self::authorizationStatusCode()]
                );
            }
        }

        return true;
    }
}
