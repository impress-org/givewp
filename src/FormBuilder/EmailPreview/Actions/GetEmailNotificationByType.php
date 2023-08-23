<?php

namespace Give\FormBuilder\EmailPreview\Actions;

/**
 * Apply preview template tags to email message.
 *
 * @since 3.0.0
 */
class GetEmailNotificationByType
{
    /**
     * @since 3.0.0
     *
     * @param $type
     *
     * @return \Give_Email_Notification|void
     * @throws \Exception
     */
    public function __invoke($type)
    {
        foreach(\Give_Email_Notifications::get_instance()->get_email_notifications() as $emailNotification) {
            if ( $type === $emailNotification->config['id'] ) {
                /* @var \Give_Email_Notification $emailNotification */
                return $emailNotification;
            }
        }

        throw new \Exception("Email notification not found for '$type'");
    }
}
