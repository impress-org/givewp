<?php

namespace Give\DonationForms\Routes\Permissions;

use Give\DonationForms\ValueObjects\DonationFormStatus;
use WP_Error;
use WP_REST_Request;

/**
 * @since 4.10.1
 */
class DonationFormPermissions
{
    /**
     * Check if current user can edit donation forms.
     *
     * @since 4.10.1
     */
    public static function canEdit(): bool
    {
        return current_user_can('manage_options') ||
               current_user_can('edit_give_forms');
    }

    /**
     * Check if current user can view private/draft donation forms.
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
     * Validate donation form access permissions for GET items (collections).
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

        // If no status is specified, allow access (defaults to published forms)
        if (empty($status)) {
            return true;
        }

        // Convert single status to array for consistent handling
        if (!is_array($status)) {
            $status = [$status];
        }

        // Check if user is trying to access any non-published forms
        $hasNonPublishedStatus = !empty(array_filter($status, function($formStatus) {
            return $formStatus !== DonationFormStatus::PUBLISHED;
        }));

        if ($hasNonPublishedStatus && !self::canViewPrivate()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to view private, draft, or trashed donation forms.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * Validate donation form access permissions for individual form GET method.
     *
     * @since 4.10.1
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForGetItem(WP_REST_Request $request)
    {
        // Individual form access validation is handled in the controller
        // where we can access the actual form data and its status
        return true;
    }

    /**
     * Validate donation form access permissions for associate forms with campaign.
     *
     * @since 4.10.1
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public static function validationForAssociateForms(WP_REST_Request $request)
    {
        if (!self::canEdit()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to associate forms with campaigns.', 'give'),
                ['status' => self::authorizationStatusCode()]
            );
        }

        return true;
    }
}
