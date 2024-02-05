<?php

namespace Give\Donors\Actions;

use Give\Donors\Exceptions\FailedDonorUserCreationException;
use Give\Donors\Models\Donor;

/**
 * @since 3.2.0
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
            throw new FailedDonorUserCreationException(
                $donor,
                0,
                new \Exception($userIdOrError->get_error_message())
            );
        }

        do_action('givewp_donor_user_created', $donor);

        $donor->save();

        return $donor;
    }
}
