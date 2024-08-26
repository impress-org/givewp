<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\EmailSettings;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;
use Give_Email_Notification_Util;
use Give_Email_Notifications;
use Give_Email_Setting_Field;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\EmailSettings
 */
class TestEmailSettings extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    public function testProcessShouldUpdateEmailSettings(): void
    {
        // Arrange
        $meta = [
            '_give_email_options' => 'enabled',
            '_give_email_template' => 'default',
            '_give_email_logo' => 'logo.png',
            '_give_from_name' => 'Charity Org',
            '_give_from_email' => 'email@example.org',
        ];

        $notifications = Give_Email_Notifications::get_instance()->get_email_notifications();
        foreach ($notifications as $notification) {
            add_filter("give_{$notification->config['id']}_get_recipients", [$this, 'getNotificationRecipients'], 1, 3);

            $prefix = '_give_' . $notification->config['id'];
            $notificationMeta = [
                $prefix . '_notification' => 'enabled',
                $prefix . '_email_subject' => $notification->config['label'],
                $prefix . '_email_header' => 'Header for: ' . $notification->config['label'],
                $prefix . '_email_message' => 'Message for: ' . $notification->config['label'],
                $prefix . '_email_content_type' => 'text/html',
            ];

            if ($notification->config['has_recipient_field']) {
                $notificationMeta[$prefix . '_recipient'] = [['email' => 'donor@charity.org']];
            }
            $meta = array_merge($meta, $notificationMeta);
        }
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, EmailSettings::class);

        // Assert
        $this->assertSame($meta['_give_email_options'], $v3Form->settings->emailOptionsStatus);
        $this->assertSame($meta['_give_email_template'], $v3Form->settings->emailTemplate);
        $this->assertSame($meta['_give_email_logo'], $v3Form->settings->emailLogo);
        $this->assertSame($meta['_give_from_name'], $v3Form->settings->emailFromName);
        $this->assertSame($meta['_give_from_email'], $v3Form->settings->emailFromEmail);

        foreach ($notifications as $notification) {
            $configId = $notification->config['id'];
            $this->assertSame('enabled', $v3Form->settings->emailTemplateOptions[$configId]['status']);
            $this->assertSame($notification->config['label'], $v3Form->settings->emailTemplateOptions[$configId]['email_subject']);
            $this->assertSame('Header for: ' . $notification->config['label'], $v3Form->settings->emailTemplateOptions[$configId]['email_header']);
            $this->assertSame('Message for: ' . $notification->config['label'], $v3Form->settings->emailTemplateOptions[$configId]['email_message']);
            $this->assertSame('text/html', $v3Form->settings->emailTemplateOptions[$configId]['email_content_type']);

            if ($notification->config['has_recipient_field']) {
                $this->assertSame(['donor@charity.org'],
                    $v3Form->settings->emailTemplateOptions[$configId]['recipient']);
            }

            remove_filter("give_{$notification->config['id']}_get_recipients", [$this, 'getNotificationRecipients'], 1);
        }
    }

    public function getNotificationRecipients($recipientEmail, $instance, $formId)
    {
        return Give_Email_Notification_Util::get_value(
            $instance,
            Give_Email_Setting_Field::get_prefix( $instance, $formId ) . 'recipient',
            $formId
        );
    }
}
