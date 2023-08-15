<?php

namespace Give\FormBuilder\EmailPreview\Actions;

/**
 * Apply preview template tags to email message.
 *
 * @since 3.0.0
 */
class ApplyPreviewTemplateTags
{
    /**
     * @since 3.0.0
     * @param  string  $message
     * @return string
     */
    public function __invoke($message): string
    {
        $parsedMessage = array_reduce( array_keys( $this->getTags() ), function ( $message, $preview_tag ) {
            return str_replace( "{{$preview_tag}}", $this->getTags()[ $preview_tag ], $message );
        }, $message );

        return apply_filters( 'give_email_preview_template_tags', $parsedMessage );
    }

    /**
     * @since 3.0.0
     * @return array
     */
    protected function getTags(): array
    {
        $user = wp_get_current_user();

        $tag_args = [
            'payment_id' => 0,
            'user_id'    => $user->ID,
        ];

        return [
                'name' => apply_filters( 'give_email_tag_first_name', $user->user_firstname, $tag_args ),
                'fullname' => apply_filters( 'give_email_tag_first_name', $user->user_lastname, $tag_args ),
                'username' => apply_filters( 'give_email_tag_first_name', $user->user_login, $tag_args ),
                'user_email' => apply_filters( 'give_email_tag_first_name', $user->user_email, $tag_args ),
                'payment_total' => give_currency_filter( '10.50' ),
                'amount' => give_currency_filter( '10.50' ),
                'price' => give_currency_filter( '10.50' ),
                'payment_method' => __( 'PayPal', 'give' ),
                'payment_id' => rand( 2000, 2050 ),
                'receipt_link_url' => give_get_receipt_url(0),
                'receipt_link' => give_get_receipt_link(0),
                'date' => date( give_date_format(), current_time( 'timestamp' ) ),
                'donation' => esc_html__( 'Sample Donation Form Title', 'give' ),
                'form_title' => esc_html__( 'Sample Donation Form Title - Sample Donation Level', 'give' ),
                'sitename' => get_bloginfo( 'name' ),
                'billing_address' => '',
                'email_access_link' => $this->getEmailAccessLink(),
                'donation_history_link' => $this->getDonationHistoryLink(),
                'reset_password_link' => '',
                'site_url' => $this->getSiteUrlLink(),
                'admin_email' => give_email_admin_email(),
                'offline_mailing_address' => give_email_offline_mailing_address(),
                'donor_comment' => esc_html__( 'Sample Donor Comment', 'give' ),
            ];
    }

    /**
     * @since 3.0.0
     * @return string
     */
    protected function getEmailAccessLink(): string
    {
        return sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(add_query_arg(['give_nl' => uniqid()], give_get_history_page_uri())),
            __( 'View your donation history &raquo;', 'give' )
        );
    }

    /**
     * @since 3.0.0
     * @return string
     */
    protected function getDonationHistoryLink(): string
    {
        return sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(add_query_arg(['give_nl' => uniqid()], give_get_history_page_uri())),
            __( 'View your donation history &raquo;', 'give' )
        );
    }

    /**
     * @since 3.0.0
     * @return string
     */
    protected function getSiteUrlLink(): string
    {
        return sprintf(
            '<a href="%1$s">%2$s</a>',
            get_bloginfo( 'url' ),
            get_bloginfo( 'url' )
        );
    }
}
