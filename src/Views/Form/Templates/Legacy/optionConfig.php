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
			Options::getContentPlacementField(),
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
