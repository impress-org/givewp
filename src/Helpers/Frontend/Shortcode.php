<?php

namespace Give\Helpers\Frontend;

class Shortcode
{
    /**
     * Get give_receipt shortcode with attributes from confirmation page.
     *
     * @since 2.7.0
     * @return string
     */
    public static function getReceiptShortcodeFromConfirmationPage()
    {
        $post = get_post(give_get_option('success_page', 0));
        $content = $post->post_content;

        if ( ! empty($content)) {
            preg_match('/\[give_receipt(.*?)]/i', $content, $matches);

            if (isset($matches[0])) {
                return $matches[0];
            }
        }

        return '';
    }

    /**
     * @since 3.4.0
     */
    public static function isValidDonation(int $donationId): bool
    {
        return ! empty($donationId) && 'give_payment' === get_post_type($donationId);
    }

    /**
     * @since 3.4.0
     */
    public static function isValidForm(int $formId): bool
    {
        return ! empty($formId) && 'give_forms' === get_post_type($formId);
    }

    /**
     * @since 3.4.0
     */
    public static function isPublishedForm(int $formId): bool
    {
        return self::isValidForm($formId) && 'publish' === get_post_status($formId);
    }
}
