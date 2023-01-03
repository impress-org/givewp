<?php

namespace Give\Email\Notifications;

use Give\Donations\ValueObjects\DonationStatus;
use Give_Email_Notification;
use Give_Payment;

/**
 * @unreleased
 */
class DonationProcessingReceipt extends Give_Email_Notification
{
    /* @var Give_Payment $payment */
    public $payment;

    /**
     * @unreleased
     */
    public function init()
    {
        // Initialize empty payment.
        $this->payment = new Give_Payment(0);

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
     * @unreleased
     *
     * @return string
     */
    public function getDefaultEmailMessage(): string
    {
        $defaultEmailMessage = give_get_default_donation_receipt_email();

        $defaultEmailMessage .= "\n\n" . '<strong>' . esc_html__('IMPORTANT:', 'give') . '</strong>' . ' ' .
                                esc_html__('this is a temporary receipt as your donation is still being processed, you will receive a final receipt once it has been completed.',
                                    'give') . "\n\n";

        /**
         * @unreleased
         */
        return apply_filters("give_{$this->config['id']}_get_default_email_message", $defaultEmailMessage);
    }

    /**
     * @unreleased
     */
    public function sendEmailNotificationToDonor(int $donationId, string $newStatus, string $oldStatus)
    {
        if ($newStatus == DonationStatus::PROCESSING()->getValue() &&
            $oldStatus !== DonationStatus::PROCESSING()->getValue()) {
            $this->payment = new Give_Payment($donationId);

            if ( ! $this->payment->ID) {
                return;
            }

            $this->recipient_email = $this->payment->email;

            $this->send_email_notification(
                [
                    'payment_id' => $this->payment->ID,
                ]
            );
        }
    }
}
