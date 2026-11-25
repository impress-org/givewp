<?php

namespace Give\DonorDashboards;

use Exception;
use Give\Donors\Models\Donor;
use WP_User;

/**
 * @since 2.10.0
 */
class Helpers
{

    /**
     * Retrieve the current donor ID from based on session
     * @since 4.20.0 Link a donor to the logged-in user when the email access token proves ownership of the account email.
     * @since 4.16.8.1 Guard against get_donor_by() finding no matching donor for the token's email.
     * @since 2.10.0
     */
    public static function getCurrentDonorId()
    {
        if (get_current_user_id()) {
            $donor = give()->donors->get_donor_by('user_id', get_current_user_id());
            if ($donor) {
                return $donor->id;
            }

            $linkedDonor = self::linkDonorFromEmailAccessToken(get_current_user_id());

            if ($linkedDonor) {
                return $linkedDonor->id;
            }
        }

        if (give()->email_access) {
            give()->email_access->init();
            $useToken = give()->email_access->check_for_token();

            if ($useToken) {
                $donor = give()->donors->get_donor_by('email', give()->email_access->token_email);

                if ($donor) {
                    return $donor->id;
                }
            }
        }

        return null;
    }

    /**
     * Retrieve donor logged in status
     *
     * @since 3.15.0 added additional user role check
     * @since 3.14.0 Add user capability and user role check
     * @since 2.20.2
     */
    public static function isDonorLoggedIn(): bool
    {
        /** @var WP_User $user */
        $user = wp_get_current_user();
        $allowedRoles = ['administrator', 'give_donor', 'give_subscriber'];
        // If the user is logged in and they are a donor, return true
        if ( is_user_logged_in() ) {
            $donor = give()->donors->get_donor_by('user_id', get_current_user_id());
            if ($donor) {
                return true;
            }
        }

        return (is_user_logged_in() && !empty(array_intersect($allowedRoles, $user->roles))) || (
                give_is_setting_enabled( give_get_option( 'email_access' ) ) &&
                Give()->email_access->is_valid_token(Give()->email_access->get_token())
        );
    }

    /**
     * Connects an unlinked donor to a logged-in user. The emailed access token proves control of the donor's
     * mailbox, so the link is made only when the token's email is also the user's account email.
     *
     * @since 4.20.0 Persist only the user association so legacy donors can have missing name metadata.
     *
     * @throws Exception
     */
    private static function linkDonorFromEmailAccessToken(int $userId): ?Donor
    {
        if (!give()->email_access || !give_is_setting_enabled(give_get_option('email_access'))) {
            return null;
        }

        give()->email_access->init();

        if (!give()->email_access->check_for_token()) {
            return null;
        }

        $user = get_userdata($userId);
        $tokenEmail = give()->email_access->token_email;

        if (!$user || !$tokenEmail || strcasecmp($user->user_email, $tokenEmail) !== 0) {
            return null;
        }

        $donor = Donor::whereEmail($tokenEmail);

        if (!$donor || $donor->userId) {
            return null;
        }

        give()->donors->updateLegacyColumns($donor->id, ['user_id' => $userId]);
        $donor->userId = $userId;

        return $donor;
    }
}
