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
			Options::getFloatLabelsField(),
			Options::getDisplayContentField(),
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
