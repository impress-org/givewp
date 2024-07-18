<?php

namespace Give\DonorDashboards;

/**
 * @since 2.10.0
 */
class Helpers
{

    /**
     * Retrieve the current donor ID from based on session
     * @since 2.10.0
     */
    public static function getCurrentDonorId()
    {
        if (get_current_user_id()) {
            $donor = give()->donors->get_donor_by('user_id', get_current_user_id());
            if ($donor) {
                return $donor->id;
            }
        }

        if (give()->email_access) {
            give()->email_access->init();
            $useToken = give()->email_access->check_for_token();

            if ($useToken) {
                $donor = give()->donors->get_donor_by('email', give()->email_access->token_email);

                return $donor->id;
            }
        }

        return null;
    }

    /**
     * Retrieve donor logged in status
     *
     * @since 2.20.2
     * @since 3.14.0 Add user capability and user role check
     */
    public static function isDonorLoggedIn(): bool
    {
        $user = wp_get_current_user();

        return is_user_logged_in() && (current_user_can('administrator') || in_array('give_donor', $user->roles)) || (
                give_is_setting_enabled( give_get_option( 'email_access' ) ) &&
                Give()->email_access->is_valid_token(Give()->email_access->get_token())
        );
    }
}
