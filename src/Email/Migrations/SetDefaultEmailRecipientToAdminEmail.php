<?php

namespace Give\Email\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give_Email_Notification_Util;
use Give_Email_Notifications;

/**
 * @unreleased
 */
class SetDefaultEmailRecipientToAdminEmail extends Migration
{
    /**
     * @inheritDoc
     */
    public function run()
    {
        $emails = Give_Email_Notifications::get_instance()->get_email_notifications();
        $giveSettings = give_get_settings();
        $settingChanged = false;

        foreach ($emails as $email) {
            if ( ! Give_Email_Notification_Util::has_recipient_field($email) ) {
                continue;
            }

            $optionName = "{$email->config['id']}_recipient";
            if (
                // This open will not exist in give_settings on fresh install
                // So we do not need to run migration because default option value handle GiveWP core.
                ! array_key_exists($optionName, $giveSettings) ||

                // If option exist and has values (valid recipients) then we do not need to run migration.
                (isset($giveSettings[$optionName]) && array_values($giveSettings[$optionName]))
            ) {
                continue;
            }

            $giveSettings[$optionName] = [get_bloginfo('admin_email')];

            $settingChanged = true;
        }

        if ( $settingChanged ) {
            update_option('give_settings', $giveSettings);
        }
    }

    /**
     * @unreleased
     * @inerhitDoc
     */
    public static function id()
    {
        return 'set-default-email-recipient-to-admin-email';
    }

    /**
     * @unreleased
     * @inerhitDoc
     */
    public static function timestamp()
    {
        return strtotime('2021-11-17');
    }
}
