<?php

namespace Give\Donors\Actions;

use Give\Donors\Models\Donor;
use Give_Donor_Register_Email;

/**
 * @since 3.2.0
 */
class SendDonorUserRegistrationNotification
{
    /**
     * @var Give_Donor_Register_Email
     */
    protected $email;

    public function __construct(Give_Donor_Register_Email $email)
    {
        $this->email = $email;
        $this->email->init();
    }

    public function __invoke(Donor $donor)
    {
        // Enable the `donor-register` (legacy) email notification.
        add_filter( "give_donor-register_is_email_notification_active", '__return_true' );

        // For legacy email notifications `setup_email_notification()` calls `send_email_notification()`.
        $this->email->setup_email_notification($donor->userId, [
            'email' => $donor->email
        ]);
    }
}
