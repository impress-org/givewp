<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class EmailSettings extends FormMigrationStep
{
    public function process()
    {
        $this->formV3->settings->emailOptionsStatus = $this->formV2->getEmailOptionsStatus();
        $this->formV3->settings->emailTemplate = $this->formV2->getEmailTemplate();
        $this->formV3->settings->emailLogo = $this->formV2->getEmailLogo();
        $this->formV3->settings->emailFromName = $this->formV2->getEmailFromName();
        $this->formV3->settings->emailFromEmail = $this->formV2->getEmailFromEmail();

        $notifications = \Give_Email_Notifications::get_instance()->get_email_notifications();
        foreach($notifications as $notification) {
            $this->formV3->settings->emailTemplateOptions[ $notification->config['id'] ] = [
                'status' => $notification->get_notification_status($this->formV2->id),
                'email_subject' => $notification->get_email_subject($this->formV2->id),
                'email_header' => $notification->get_email_header($this->formV2->id),
                'email_message' => str_replace(
                    ['"“', '"”', '“"', '”"', '“', '”'],
                    '"',
                    $notification->get_email_message($this->formV2->id)
                ),
                'email_content_type' => $notification->get_email_content_type($this->formV2->id),
                'recipient' => (array) $notification->get_recipient($this->formV2->id)
            ];
        }
    }
}
