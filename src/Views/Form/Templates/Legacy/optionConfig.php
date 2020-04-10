<?php

use Give\Form\Template\Options;

$price_placeholder = give_format_decimal( '1.00', false, false );

return [
	'display_settings' => [
		'name'   => __( 'Form Display', 'give' ),
		'desc'   => __( 'Step description will show up here if any', 'give' ),
		'fields' => [
			Options::getDonationLevelsDisplayStyleField(),
			Options::getDisplayOptionsField(),
			Options::getContinueToDonationFormField(),
			Options::getCheckoutLabelField(),
			[
				'name'    => __( 'Floating Labels', 'give' ),
				/* translators: %s: forms http://docs.givewp.com/form-floating-labels */
				'desc'    => sprintf( __( 'Select the <a href="%s" target="_blank">floating labels</a> setting for this GiveWP form. Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.', 'give' ), esc_url( 'http://docs.givewp.com/form-floating-labels' ) ),
				'id'      => 'form_floating_labels',
				'type'    => 'radio_inline',
				'options' => [
					'global'   => __( 'Global Option', 'give' ),
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				],
				'default' => 'global',
			],
			[
				'name'          => __( 'Display Content', 'give' ),
				'description'   => __( 'Do you want to add custom content to this form?', 'give' ),
				'id'            => 'display_content',
				'type'          => 'radio_inline',
				'options'       => [
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				],
				'wrapper_class' => '_give_display_content_field',
				'default'       => 'disabled',
			],
			[
				'name'          => __( 'Content Placement', 'give' ),
				'description'   => __( 'This option controls where the content appears within the donation form.', 'give' ),
				'id'            => 'content_placement',
				'type'          => 'radio_inline',
				'options'       => apply_filters(
					'give_forms_content_options_select',
					[
						'give_pre_form'  => __( 'Above fields', 'give' ),
						'give_post_form' => __( 'Below fields', 'give' ),
					]
				),
				'wrapper_class' => '_give_content_placement_field give-hidden',
				'default'       => 'give_pre_form',
			],
			[
				'name'          => __( 'Content', 'give' ),
				'description'   => __( 'This content will display on the single give form page.', 'give' ),
				'id'            => 'form_content',
				'type'          => 'wysiwyg',
				'wrapper_class' => '_give_form_content_field give-hidden',
			],
		],
	],
];
