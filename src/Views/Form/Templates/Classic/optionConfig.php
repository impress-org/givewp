<?php

return [
    /**
     * Visual Appearance
     */
    'visual_appearance' => [
        'name'   => sprintf(__('%1$sVisual Appearance%2$s', 'give'), '<strong>', '</strong>'),
        'fields' => [
            [
                'id'      => 'primary_color',
                'name'    => __('Primary Color', 'give'),
                'desc'    => __('The primary color is used throughout the Form Template for various elements including buttons, line breaks, and focus/hover elements. Set a color that reflects your brand or website colors for best results.', 'give'),
                'type'    => 'colorpicker',
                'default' => '#1E8CBE',
            ],
            [
                'id'      => 'container_style',
                'name'    => __('Container Style', 'give'),
                'desc'    => __('Do you want your donation form to be in a white container or without a container so that it blends into your website’s background?', 'give'),
                'type'    => 'radio',
                'options' => [
                    'boxed'   => __('Boxed', 'give'),
                    'unboxed' => __('Unboxed', 'give'),
                ],
                'default' => 'boxed',
            ],
            [
                'id'         => 'primary_font',
                'name'       => __('Primary Font', 'give'),
                'desc'       => __('A custom Google Font can make the donation form look great but can extra page load. The Theme Font option will use your theme’s font. The System Font option uses the system font of a particular operating system and can boost performance.', 'give'),
                'type'       => 'radio',
                'options'    => [
                    'montserrat' => __('Montserrat Google Font', 'give'),
                    'custom'     => __('Custom Google Font', 'give'),
                    'system'     => __('User\'s System Font', 'give'),
                ],
                'attributes' => [
                    'class' => 'give-visibility-handler',
                ],
                'default'    => 'montserrat',
            ],
            [
                'id'         => 'custom_font',
                'name'       => __('Google Font', 'give'),
                'desc'       => '',
                'type'       => 'select',
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[appearance][primary_font]' => 'custom' ])),
                ],
                'options'    => [
                    'list' => 'List of fonts',
                ],
                'default'    => '',
            ],
            [
                'id'      => 'display_header',
                'name'    => __('Display Header', 'give'),
                'desc'    => __('If enabled, a headline and description show above the donation fields.', 'give'),
                'type'    => 'radio',
                'options' => [
                    'enabled'  => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
                'attributes' => [
                    'class' => 'give-visibility-handler',
                ],
                'default' => 'enabled',
            ],
            [
                'id'      => 'main_heading',
                'name'    => __('Main Heading', 'give'),
                'desc'    => __('This is the text that displays for the security badge.', 'give'),
                'type'    => 'text',
                'default' => __('Support Our Cause', 'give'),
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[appearance][display_header]' => 'enabled' ])),
                ],
            ],
            [
                'id'      => 'description',
                'name'    => __('Description', 'give'),
                'desc'    => __('The description displays below the headline, and defaults to the Donation Form\'s excerpt, if present. Best practice: limit the description to short sentences that drive the donor toward the next step.', 'give'),
                'type'    => 'textarea',
                'default' => __('Help our organization by donating today! All donations go directly to making a difference for our cause.', 'give'),
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[appearance][display_header]' => 'enabled' ])),
                ],
            ],
            [
                'id'   => 'header_background_image',
                'name' => __('Header Background Image', 'give'),
                'desc' => __('Upload an eye-catching image that reflects your cause. For best results use an image in 16x9 aspect ratio at least 855x480px.', 'give'),
                'type' => 'file',
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[appearance][display_header]' => 'enabled' ])),
                ],
            ],
            [
                'id'      => 'secure_badge',
                'name'    => __('Secure Donation Badge', 'give'),
                'desc'    => __('If enabled, a badge will display show in the header providing a security reassurement for donors.', 'give'),
                'type'    => 'radio',
                'attributes' => [
                    'class' => 'give-visibility-handler',
                ],
                'options' => [
                    'enabled'  => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
                'default' => 'enabled',
            ],
            [
                'id'      => 'secure_badge_text',
                'name'    => __('Secure Donation Badge Text', 'give'),
                'desc'    => __('This is the text that displays for the security badge.', 'give'),
                'type'    => 'text',
                'default' => __('100% Secure Donation', 'give'),
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[appearance][secure_badge]' => 'enabled' ])),
                ],
            ],
            [
                'id' => 'decimals_enabled',
                'name' => __('Decimal amounts', 'give'),
                'desc' => __(
                    'Do you want to enable decimal amounts? When the setting is disabled, decimal values are rounded.',
                    'give'
                ),
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'disabled' => __('Disabled', 'give'),
                    'enabled' => __('Enabled', 'give'),
                ],
            ],
        ],
    ],

    /**
     * Section 1: Donation Amount
     */
    'donation_amount' => [
        'name'   => sprintf(__('%1$sSection 1:%2$s Donation Amount', 'give'), '<strong>', '</strong>'),
        'fields' => [
            [
                'id'      => 'headline',
                'name'    => __('Headline', 'give'),
                'desc'    => __('The Headline displays before the donation level amounts, and is designed to provide context for those levels. Best practice: limit this to 1 sentence crafted to drive the donor to decide and to remove friction. Leave blank to remove.', 'give'),
                'type'    => 'text',
                'default' => __('How much would you like to donate today?', 'give'),
            ],
            [
                'id'      => 'description',
                'name'    => __('Description', 'give'),
                'desc'    => __('The description displays below the headline and is designed to remove obstacles from donating. Best practice: use this section to reassure donors that they are making a wise decision. Leave blank to remove. Leave blank to remove.', 'give'),
                'type'    => 'textarea',
                'default' => __('All donations directly impact our organization and help us further our mission.', 'give'),
            ],
        ],
    ],

    /**
     * Section 2: Donor Information
     */
    'donor_information' => [
        'name'   => sprintf(__('%1$sSection 2:%2$s Donor Information', 'give'), '<strong>', '</strong>'),
        'fields' => [
            [
                'id'      => 'headline',
                'name'    => __('Headline', 'give'),
                'desc'    => __('The Headline introduces the section where donors provide information about themselves. Best practice: limit the headline to fewer than one short sentence. Leave blank to remove.', 'give'),
                'type'    => 'text',
                'default' => __('Who\'s giving today?', 'give'),
            ],
            [
                'id'      => 'description',
                'name'    => __('Description', 'give'),
                'desc'    => __('The description displays below the headline and is designed to remove obstacles from donating. Best practice: use this section to reassure donors that their information is secure. Leave blank to remove.', 'give'),
                'type'    => 'textarea',
                'default' => __('We’ll never share this information with anyone.', 'give'),
            ],
        ],
    ],


    /**
     * Section 3: Payment Method
     */
    'payment_information' => [
        'name'   => sprintf(__('%1$sSection 3:%2$s Payment Method', 'give'), '<strong>', '</strong>'),
        'fields' => [
            [
                'id'      => 'headline',
                'name'    => __('Headline', 'give'),
                'desc'    => __('The Headline introduces the section where donors provide payment information. Best practice: limit the headline to one short sentence. Leave blank to remove.', 'give'),
                'type'    => 'text',
                'default' => __('How would you like to pay today?', 'give'),
            ],
            [
                'id'      => 'description',
                'name'    => __('Description', 'give'),
                'desc'    => __('The description displays below the headline and is designed to remove obstacles from donating. Best practice: use this section to reassure donors that their information is secure. Leave blank to remove.', 'give'),
                'type'    => 'textarea',
                'default' => __('This donation is a secure and encrypted payment.', 'give'),
            ],
            [
                'id' => 'donation_summary_enabled',
                'name' => __('Donation Summary', 'give'),
                'desc' => __('Display a summary of the donation on the Payment Information step', 'give'),
                'type' => 'radio_inline',
                'options' => [
                    'enabled' => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
                'default' => 'enabled',
            ],
            [
                'id' => 'donation_summary_heading',
                'name' => __('Summary Heading', 'give'),
                'desc' => __(
                    'This text displays above the donation summary table and is designed to help introduce the donation breakdown to the visitor. Leave blank to remove.',
                    'give'
                ),
                'type' => 'text',
                'default' => __('Here\'s what you\'re about to donate:', 'give'),
            ],
            [
                'id' => 'donation_summary_location',
                'name' => __('Summary Location', 'give'),
                'desc' => __('Where should the donation summary be placed?', 'give'),
                'type' => 'radio_inline',
                'options' => [
                    'give_donation_form_user_info' => __('Before Payment Fields', 'give'),
                    'give_donation_form_before_submit' => __('After Payment Fields', 'give'),
                ],
                'default' => 'give_donation_form_before_submit',
            ],
        ],
    ],


    /**
     * Donation Receipt and Thank You
     */
    'donation_receipt' => [
        'name'   => __('Donation Receipt and Thank You', 'give'),
        'fields' => [
            [
                'id'      => 'headline',
                'name'    => __('Headline', 'give'),
                'desc'    => __('This message displays in large font on the thank you screen. Best practice: short, sweet, and sincere works best.', 'give'),
                'type'    => 'text',
                'default' => __('Hey Ben, thanks for your donation! 🙏', 'give'),
            ],
            [
                'id'      => 'description',
                'name'    => __('Description', 'give'),
                'desc'    => __('The description is displayed directly below the main headline and should be 1-2 sentences. You may use any of the available template tags within this message.', 'give'),
                'type'    => 'textarea',
                'default' => __('{name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donor_email}.', 'give'),
            ],
            [
                'id'      => 'social_sharing',
                'name'    => __('Social Sharing', 'give'),
                'desc'    => __('Enable to display links for donors to share on social media that they donated.', 'give'),
                'type'    => 'radio',
                'attributes' => [
                    'class' => 'give-visibility-handler',
                ],
                'options' => [
                    'enabled'  => __('Enabled', 'give'),
                    'disabled' => __('Disabled', 'give'),
                ],
                'default' => 'enabled',
            ],
            [
                'id'      => 'sharing_instructions',
                'name'    => __('Sharing Instruction', 'give'),
                'desc'    => __('Sharing instructions display above the social sharing buttons. Best practice: be direct, bold, and confident here. Donors share when they are asked to.', 'give'),
                'type'    => 'text',
                'default' => __('Help spread the word by sharing your support with your friends and followers!', 'give'),
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[donation_receipt][social_sharing]' => 'enabled' ])),
                ],
            ],
            [
                'id'      => 'twitter_message',
                'name'    => __('Twitter Message', 'give'),
                'desc'    => __('This puts "words in the mouth" of your donor to share with their Twitter followers.', 'give'),
                'type'    => 'text',
                'default' => __('Help spread the word by sharing your support with your friends and followers!', 'give'),
                'attributes' => [
                    'data-field-visibility' => htmlspecialchars(json_encode([ 'classic[donation_receipt][social_sharing]' => 'enabled' ])),
                ],
            ],
        ],
    ],
];
