<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\DonationNote;

use function do_action;

class DispatchDonationNoteEmailNotification
{

    /**
     * @since 2.25.0
     */
    public function __invoke(DonationNote $donationNote)
    {
        do_action('give_donor-note_email_notification', $donationNote->id, $donationNote->donationId);
    }
}
