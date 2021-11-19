<?php

namespace Give\Onboarding;

use Give\Form\Template\Options as TemplateOptions;

/**
 * @since 2.8.0
 */
class DefaultFormFactory
{

    /**
     * Green to match the Onboarding Wizard.
     *
     * @since 2.8.0
     * @var string;
     *
     */
    protected $primaryColor = '#4fa651';

    /**
     * @since 2.8.0
     * @return int $formID
     *
     */
    public function make()
    {
        $formID = wp_insert_post(
            [
                'post_title' => 'Donation Form',
                'post_type' => 'give_forms',
                'post_status' => 'publish', // @TODO: Preview needs to work with Draft status.
                'meta_input' => [
                    '_give_onboarding_default_form' => 1,
                    '_give_levels_minimum_amount' => 10,
                    '_give_levels_maximim_amount' => 250,
                    '_give_form_template' => 'sequoia',
                    '_give_form_status' => 'open',
                    '_give_sequoia_form_template_settings' => $this->getTemplateConfig(),
                    '_give_checkout_label' => __('Donate Now', 'give'),
                    '_give_display_style' => 'buttons',
                    '_give_payment_display' => 'button',
                    '_give_form_floating_labels' => 'disabled',
                    '_give_reveal_label' => __('Donate Now', 'give'),
                    '_give_display_content' => 'disabled',
                    '_give_content_placement' => '',
                    '_give_form_content' => '',
                    '_give_price_option' => 'multi',
                    '_give_set_price' => 1,
                    '_give_custom_amount' => 'enabled',
                    '_give_donation_levels' => $this->getDonationLevels(),
                    '_give_default_gateway' => 'global',
                    '_give_name_title_prefix' => 'global',
                    '_give_title_prefixes' => '',
                    '_give_company_field' => 'global',
                    '_give_anonymous_donation' => 'global',
                    '_give_donor_comment' => 'global',
                    '_give_logged_in_only' => 'enabled',
                    '_give_show_register_form' => 'none',
                    '_give_goal_option' => 'disabled',
                    '_give_goal_format' => 'amount',
                    '_give_set_goal' => 10000,
                    '_give_number_of_donor_goal' => 100,
                    '_give_goal_color' => $this->primaryColor,
                    '_give_close_form_when_goal_achieved' => 'disabled',
                    '_give_form_goal_achieved_message' => __(
                        'Thank you to all our donors, we have met our fundraising goal.',
                        'give'
                    ),
                    '_give_terms_option' => 'global',
                    '_give_agree_label' => __('Agree to terms?', 'give'),
                    '_give_agree_text' => __('The terms can be customized in the donation form settings.', 'give'),
                    'give_stripe_per_form_accounts' => 'disabled', // Note: Doesn't use underscore prefix.
                    '_give_default_stripe_account' => '',
                    '_give_email_options' => 'global',
                    '_give_email_template' => 'default',
                    '_give_email_logo' => '',
                    '_give_from_name' => get_bloginfo('name'),
                    '_give_from_email' => get_bloginfo('admin_email'),
                    '_give_new-donation_notification' => 'global',
                    '_give_new-donation_email_subject' => sprintf('%s - #{payment_id}', __('New Donation', 'give')),
                    '_give_new-donation_email_header' => __('New Donation!', 'give'),
                    '_give_new-donation_email_message' => give_get_default_donation_notification_email(),
                    '_give_new-donation_email_content_type' => 'text/html',
                    '_give_new-donation_recipient' => [
                        'email' => get_bloginfo('admin_email'),
                    ],
                    '_give_donation-receipt_notification' => 'global',
                    '_give_donation-receipt_email_subject' => __('Donation Receipt', 'give'),
                    '_give_donation-receipt_email_header' => __('Donation Receipt', 'give'),
                    '_give_donation-receipt_email_mesage' => give_get_default_donation_receipt_email(),
                    '_give_donation-receipt_email_content_type' => 'text/html',
                    '_give_form_goal_progress' => -1,
                    '_give_offline_checkout_notes' => '<em>You can customize instructions in the forms settings.</em>'
                                                      . '<br /><br />'
                                                      . '<strong>Please make checks payable to "{sitename}".</strong>'
                                                      . '<br /><br />'
                                                      . 'Your donation is greatly appreciated!',
                ],
            ]
        );

        return $formID;
    }

    /**
     * Default values extracted from src/Views/Form/Templates/Sequoia/optionConfig.php
     *
     * Updates the default primary_color to match the Onboarding Wizard.
     *
     * @since 2.16.2 add new visual appearance settings
     * @since 2.8.0
     * @return array
     *
     */
    public function getTemplateConfig()
    {
        return [
            'introduction' => [
                'enabled' => 'enabled',
                'headline' => __('Support Our Cause', 'give'),
                'description' => __(
                    'Help our organization by donating today! All donations go directly to making a difference for our cause.',
                    'give'
                ),
                'image' => GIVE_PLUGIN_URL . 'assets/dist/images/onboarding-preview-form-image.min.jpg',
                'primary_color' => $this->primaryColor,
                'donate_label' => __('Donate Now', 'give'),
            ],
            'payment_amount' => [
                'header_label' => __('Choose Amount', 'give'),
                'content' => sprintf(
                    __(
                        'How much would you like to donate? As a contributor to %s we make sure your donation goes directly to supporting our cause.',
                        'give'
                    ),
                    get_bloginfo('sitename')
                ),
                'next_label' => __('Continue', 'give'),
            ],
            'visual_appearance' => [
                'decimals_enabled' => 'disabled',
                'primary_color' => '#28C77B',
                'google-fonts' => 'enabled',
            ],
            'payment_information' => [
                'header_label' => __('Add Your Information', 'give'),
                'headline' => __("Who's giving today?", 'give'),
                'description' => __('We’ll never share this information with anyone.', 'give'),
                'donation_summary_enabled' => 'enabled',
                'donation_summary_heading' => __('Here\'s what you\'re about to donate:', 'give'),
                'donation_summary_location' => 'give_donation_form_before_submit',
                TemplateOptions::getCheckoutLabelField(),
            ],
            'thank-you' => [
                'image' => '',
                'headline' => __('A great big thank you!', 'give'),
                'description' => __(
                    '{name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donor_email}. ',
                    'give'
                ),
                'sharing' => 'enabled',
                'sharing_instruction' => __(
                    'Help spread the word by sharing your support with your friends and followers!',
                    'give'
                ),
                'twitter_message' => __("I just gave to this cause. Who's next?", 'give'),
            ],
        ];
    }

    /**
     * Default values forked from includes/admin/forms/class-metabox-form-data.php
     *
     * @since 2.8.0
     * @since 2.13.3 Donation level id and amount value changed to string
     * @return array
     *
     */
    public function getDonationLevels()
    {
        return [
            [
                '_give_id' =>
                    [
                        'level_id' => '0',
                    ],
                '_give_amount' => give_sanitize_amount_for_db(10),
            ],
            [
                '_give_id' =>
                    [
                        'level_id' => '1',
                    ],
                '_give_amount' => give_sanitize_amount_for_db(25),
            ],
            [
                '_give_id' =>
                    [
                        'level_id' => '2',
                    ],
                '_give_amount' => give_sanitize_amount_for_db(50),
            ],
            [
                '_give_id' =>
                    [
                        'level_id' => '3',
                    ],
                '_give_amount' => give_sanitize_amount_for_db(100),
                '_give_default' => 'default',
            ],
            [
                '_give_id' =>
                    [
                        'level_id' => '5',
                    ],
                '_give_amount' => give_sanitize_amount_for_db(250),
            ],
        ];
    }
}
