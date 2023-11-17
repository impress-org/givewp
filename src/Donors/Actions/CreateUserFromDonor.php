<?php

namespace Give\Donors\Actions;

use Give\Donors\Models\Donor;

/**
 * @unreleased
 */
class CreateUserFromDonor
{
    public function __invoke(Donor $donor): Donor
    {
        $userIdOrError = wp_insert_user(apply_filters(
            'givewp_create_donor_new_user',
            [
                'user_login'      => $donor->email,
                'user_pass'       => wp_generate_password(),
                'user_email'      => $donor->email,
                'first_name'      => $donor->firstName,
                'last_name'       => $donor->lastName,
                'role'            => give_get_option( 'donor_default_user_role', 'give_donor' ),
            ],
            $donor
        ));

        if(!is_wp_error($userIdOrError)) {
            $donor->userId = $userIdOrError;
        } else {
            // How should we handle this?
            throw new \Exception('Could not create user from donor');
        }

        do_action('givewp_donor_user_created', $donor);

        $donor->save();

        return $donor;
    }
}
