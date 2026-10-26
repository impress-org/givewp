<?php

namespace Give\Email\Notifications;

use Give\Donations\Models\Donation;
use Give_Email_Notification;

/**
 * Notifies a donor when Stripe requires ACH microdeposit verification before a donation can complete.
 *
 * Microdeposits take one to two business days to appear on the donor's bank statement, so the donor
 * cannot finish verification immediately. The hosted Stripe verification URL is emailed instead of
 * redirecting the donor to it.
 *
 * @since TBD
 */
class DonationMicrodepositVerificationEmail extends Give_Email_Notification
{
    /**
     * @since TBD
     */
    public function init()
    {
        $this->load(
            [
                'id' => 'donation-microdeposit-verification',
                'label' => __('ACH Microdeposit Verification', 'give'),
                'description' => __(
                    'Sent to the donor when Stripe requires ACH bank account microdeposit verification.',
                    'give'
                ),
                'notification_status' => 'enabled',
                'form_metabox_setting' => true,
                'recipient_group_name' => __('Donor', 'give'),
                'default_email_subject' => esc_attr__('Action required: verify your bank account', 'give'),
                'default_email_message' => $this->getDefaultEmailMessage(),
                'default_email_header' => __('Verify your bank account', 'give'),
            ]
        );
    }

    /**
     * @since TBD
     */
    public function getDefaultEmailMessage(): string
    {
        $defaultEmailMessage = sprintf(
            /* translators: %s: Donor name email tag */
            esc_html__('Dear %s!', 'give') . "\n\n" .
            esc_html__(
                'Thank you for your donation. Your bank has requested additional verification for your ACH payment, so your donation is on hold until it is complete.',
                'give'
            ) . "\n\n" .
            esc_html__(
                'Stripe has sent one or two microdeposits to your bank account. They take 1-2 business days to appear on your statement. Use the link below to verify the account:',
                'give'
            ) . "\n\n" .
            '<a href="%s">%s</a>' . "\n\n" .
            esc_html__('Here are the details of your donation:', 'give') . "\n\n" .
            '<strong>' . esc_html__('Donation:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Amount:', 'give') . '</strong>' . ' %s' . "\n" .
            '<strong>' . esc_html__('Donation ID:', 'give') . '</strong>' . ' %s' . "\n\n" .
            esc_html__('Once your bank account is verified, your donation will be processed automatically.', 'give') . "\n\n" .
            esc_html__('Sincerely,', 'give') . "\n" .
            '%s',
            '{name}',
            '{microdeposit_verification_url}',
            '{microdeposit_verification_url}',
            '{donation}',
            '{amount}',
            '{payment_id}',
            '{sitename}'
        );

        /**
         * @since TBD
         */
        return apply_filters("give_{$this->config['id']}_get_default_email_message", $defaultEmailMessage);
    }

    /**
     * Registers the hosted verification URL email tag used in the default message.
     *
     * @since TBD
     */
    public function registerEmailTag()
    {
        give_add_email_tag(
            [
                'tag' => 'microdeposit_verification_url',
                'desc' => __(
                    'The hosted Stripe URL the donor uses to verify their bank account microdeposits.',
                    'give'
                ),
                'context' => 'donation',
                'func' => static function ($args) {
                    if (empty($args['payment_id'])) {
                        return '';
                    }

                    $url = give_get_meta(absint($args['payment_id']), '_give_stripe_microdeposit_verification_url', true);

                    return $url ? esc_url($url) : '';
                },
            ]
        );
    }

    /**
     * Sends the verification email to the donor.
     *
     * @since TBD
     */
    public function sendEmailNotificationToDonor(Donation $donation): bool
    {
        if ('disabled' === $this->get_notification_status()) {
            return false;
        }

        $this->recipient_email = $donation->email;

        return $this->send_email_notification(['payment_id' => $donation->id]);
    }
}
