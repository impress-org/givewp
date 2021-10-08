<?php

use Give\Form\Template\Options;
use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;

global $pagenow;
$formInfo = get_post( FrontendFormTemplateUtils::getFormId() );

// Setup dynamic defaults
$introHeadline    = ( ! $formInfo->post_title || 'post-new.php' === $pagenow ) ? __( 'Support Our Cause', 'give' ) : $formInfo->post_title;
$introDescription = $formInfo->post_excerpt ? $formInfo->post_excerpt : __( 'Help our organization by donating today! All donations go directly to making a difference for our cause.', 'give' );

return [
	'introduction'        => [
		'name'   => sprintf( __( '%1$s Step 1: %2$s Introduction', 'give' ), '<strong>', '</strong>' ),
		'desc'   => __( 'Step description goes here.', 'give' ),
		'fields' => [
			[
				'name'    => __( 'Include Step One', 'give' ),
				'desc'    => __( 'If enabled, a headline and description show for the first step of the donation process.', 'give' ),
				'id'      => 'enabled',
				'type'    => 'radio_inline',
				'options' => [
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				],
				'default' => 'enabled',
			],
			[
				'id'         => 'headline',
				'name'       => __( 'Headline', 'give' ),
				'desc'       => __( 'The headline displays at the top of step one, and defaults to the Form Title. Best practice: limit the headline to fewer than 8 words.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => $introHeadline,
				],
				'default'    => $introHeadline,
			],
			[
				'id'         => 'description',
				'name'       => __( 'Description', 'give' ),
				'desc'       => __( 'The description displays below the headline, and defaults to the Donation Form\'s excerpt, if present. Best practice: limit the description to short sentences that drive the donor toward the next step.', 'give' ),
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => $introDescription,
				],
				'default'    => $introDescription,
			],
			[
				'id'   => 'image',
				'name' => __( 'Image', 'give' ),
				'desc' => __( 'Upload an eye-catching image that reflects your cause. For best results use an image in 16x9 aspect ratio at least 680x400px.', 'give' ),
				'type' => 'file',
			],
			[
				'id'      => 'primary_color',
				'name'    => __( 'Primary Color', 'give' ),
				'desc'    => __( 'The primary color is used throughout the Form Template for various elements including buttons, line breaks, and focus/hover elements. Set a color that reflects your brand or main featured image for best results.', 'give' ),
				'type'    => 'colorpicker',
				'default' => '#28C77B',
			],
			[
				'id'         => 'donate_label',
				'name'       => __( 'Donate Button', 'give' ),
				'desc'       => __( 'Customize the text that appears prompting the user to go to the next step.', 'give' ),
				'type'       => 'text_medium',
				'attributes' => [
					'placeholder' => __( 'Donate Now', 'give' ),
				],
				'default'    => __( 'Donate Now', 'give' ),
			],
		],
	],
	'payment_amount'      => [
		'name'   => sprintf( __( '%1$s Step 2: %2$s Payment Amount', 'give' ), '<strong>', '</strong>' ),
		'desc'   => __( 'Step description goes here.', 'give' ),
		'fields' => [
			[
				'id'         => 'header_label',
				'name'       => __( 'Header Label', 'give' ),
				'desc'       => __( 'The Header Label displays at the top of this step, and is designed to focus the donor\'s attention on what this step is about. Best Practice: limit this to fewer than 4 words.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Choose Amount', 'give' ),
				],
				'default'    => __( 'Choose Amount', 'give' ),
			],
			[
				'id'         => 'content',
				'name'       => __( 'Content', 'give' ),
				'desc'       => __( 'Content displays before the level amounts, and is designed to provide context for those levels. Best practice: limit this to 1-2 short sentences crafted to drive the donor to decide and to remove friction.', 'give' ),
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => sprintf( __( 'How much would you like to donate? As a contributor to %s we make sure your donation goes directly to supporting our cause.', 'give' ), get_bloginfo( 'sitename' ) ),
				],
			],
			[
				'id'         => 'next_label',
				'name'       => __( 'Continue Button', 'give' ),
				'desc'       => __( 'Customize the text that appears prompting the user to go to the next step.', 'give' ),
				'type'       => 'text_medium',
				'attributes' => [
					'placeholder' => __( 'Continue', 'give' ),
				],
				'default'    => __( 'Continue', 'give' ),
			],
			[
				'id'      => 'decimals_enabled',
				'name'    => __( 'Decimal amounts', 'give' ),
				'desc'    => __( 'Do you want to enable decimal amounts? When the setting is disabled, decimal values are rounded.', 'give' ),
				'type'    => 'radio_inline',
				'default' => 'disabled',
				'options' => [
					'disabled' => __( 'Disabled', 'give' ),
					'enabled'  => __( 'Enabled', 'give' ),
				],
			],
		],
	],
	'payment_information' => [
		'name'   => sprintf( __( '%1$s Step 3: %2$s Payment Information', 'give' ), '<strong>', '</strong>' ),
		'desc'   => __( 'Step description goes here.', 'give' ),
		'fields' => [
			[
				'id'         => 'header_label',
				'name'       => __( 'Header Label', 'give' ),
				'desc'       => __( 'The Header Label displays at the top of this step, and is designed to focus the donor\'s attention on what this step is about. Best Practice: limit this to fewer than 4 words.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Add Your Information', 'give' ),
				],
				'default'    => __( 'Add Your Information', 'give' ),
			],
			[
				'id'         => 'headline',
				'name'       => __( 'Headline', 'give' ),
				'desc'       => __( 'The Headline introduces the section where donors provide information about themselves. Best practice: limit the headline to fewer than 5 words.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( "Who's giving today?", 'give' ),
				],
				'default'    => __( "Who's giving today?", 'give' ),
			],
			[
				'id'         => 'description',
				'name'       => __( 'Description', 'give' ),
				'desc'       => __( 'The description displays below the checkout step, and is designed to remove obstacles from donating. Best practice: use this section to reassure donors that they are making a wise decision.', 'give' ),
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => __( 'We’ll never share this information with anyone.', 'give' ),
				],
				'default'    => __( 'We’ll never share this information with anyone.', 'give' ),
			],
			Options::getCheckoutLabelField(),
		],
	],
	'thank-you'           => [
		'name'   => sprintf( __( '%1$s Step 4: %2$s Thank You', 'give' ), '<strong>', '</strong>' ),
		'desc'   => __( 'Step description goes here.', 'give' ),
		'fields' => [
			[
				'id'   => 'image',
				'name' => __( 'Image', 'give' ),
				'desc' => __( 'This image appears above the main thank you content. If no image is provided, a check mark icon will appear. For best results use an image in 16x9 aspect ratio at least 680x400px.', 'give' ),
				'type' => 'file',
			],
			[
				'id'         => 'headline',
				'name'       => __( 'Headline', 'give' ),
				'desc'       => __( 'This message displays in large font on the thank you screen. Best practice: short, sweet, and sincere works best.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'A great big thank you!', 'give' ),
				],
				'default'    => __( 'A great big thank you!', 'give' ),
			],
			[
				'id'         => 'description',
				'name'       => __( 'Description', 'give' ),
				'desc'       => __( 'The description is displayed directly below the main headline and should be 1-2 sentences. You may use <a href="http://docs.givewp.com/email-tags" target="_blank">any of the available template tags</a> within this message.', 'give' ),
				'type'       => 'wysiwyg',
				'attributes' => [
					'placeholder' => __( '{name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
				],
				'default'    => __( '{name}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
			],
			[
				'name'    => __( 'Social Sharing', 'give' ),
				'desc'    => __( 'Enable to display links for donors to share on social media that they donated.', 'give' ),
				'id'      => 'sharing',
				'type'    => 'radio_inline',
				'options' => [
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				],
				'default' => 'enabled',
			],
			[
				'id'         => 'sharing_instruction',
				'name'       => __( 'Sharing Instruction', 'give' ),
				'desc'       => __( 'Sharing instructions display above the social sharing buttons. Best practice: be direct, bold, and confident here. Donors share when they are asked to.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Help spread the word by sharing your support with your friends and followers!', 'give' ),
				],
				'default'    => __( 'Help spread the word by sharing your support with your friends and followers!', 'give' ),
			],
			[
				'id'         => 'twitter_message',
				'name'       => __( 'Twitter Message', 'give' ),
				'desc'       => __( 'This puts "words in the mouth" of your donor to share with their Twitter followers.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'I just gave to this cause. Who\'s next?', 'give' ),
				],
				'default'    => __( 'I just gave to this cause. Who\'s next?', 'give' ),
			],
		],
	],
];
