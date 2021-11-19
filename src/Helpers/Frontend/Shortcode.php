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
}
