<?php

namespace Give\Email\Notifications;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give_Email_Notification;

/**
 * @since 2.24.0
 */
class DonationProcessingReceipt extends Give_Email_Notification
{
    /**
     * @since 2.24.0
     */
    public function init()
    {
        $this->load(
            [
                'id' => 'donation-processing-receipt',
                'label' => __('Donation Processing Receipt', 'give'),
                'description' => __('Sent to the donor when their donation is marked as processing.',
                    'give'),
                'notification_status' => 'enabled',
                'form_metabox_setting' => true,
                'recipient_group_name' => __('Donor', 'give'),
                'default_email_subject' => esc_attr__('Donation Processing Receipt', 'give'),
                'default_email_message' => $this->getDefaultEmailMessage(),
                'default_email_header' => __('Donation Processing Receipt', 'give'),
            ]
        );

        if ('disabled' != $this->get_notification_status()) {
            add_action('give_update_payment_status', [$this, 'sendEmailNotificationToDonor'], 10, 3);
        }
    }

    /**
     * @since 2.24.0
     */
    public function getDefaultEmailMessage(): string
    {
        $defaultEmailMessage = sprintf(
            esc_html__('Dear %s!', 'give') . "\n\n" .
            esc_html__('Thank you for your donation. Your payment is currently being processed and you will receive a final email receipt once it has completed. Your generosity is appreciated!',
                'give') . "\n\n" .
            esc_html__('Here are the details of your donation:', 'give') . "\n\n" .
            '<strong>' . esc_html__('Donor:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Donation:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Donation Date:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Amount:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Payment Method:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Payment ID:', 'give') . '</strong>' . ' %s' . "\n\n" .
            '%s' . "\n\n" .
            esc_html__('Sincerely ', 'give') . "\n" .
            '%s' . "\n"
            , '{name}', '{fullname}', '{donation}', '{date}', '{amount}', '{payment_method}',
            '{payment_id}', '{receipt_link}', '{sitename}');

        /**
         * @since 2.24.0
         */
        return apply_filters("give_{$this->config['id']}_get_default_email_message", $defaultEmailMessage);
    }

    /**
     * @since 2.24.0
     */
    public function sendEmailNotificationToDonor(int $donationId, string $newStatus, string $oldStatus)
    {
        if ($newStatus == DonationStatus::PROCESSING()->getValue() &&
            $oldStatus !== DonationStatus::PROCESSING()->getValue()) {
            $donation = Donation::find($donationId);

            if ( ! $donation) {
                return;
            }

            $this->recipient_email = $donation->email;

            $this->send_email_notification(
                [
                    'payment_id' => $donation->id,
                ]
            );
        }
    }
}
