<?php
namespace Give\Form\Template;

use Give\FormAPI\Group;

/**
 * Class Options
 *
 * @since 2.7.0
 * @package Give\Form\Template
 */
class Options {
	/**
	 * Theme Options
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $groups = [];

	/**
	 * ThemeOptions constructor.
	 *
	 * @since 2.7.0
	 * @param $array
	 *
	 * @return Options
	 */
	public static function fromArray( $array ) {
		$options = new static();

		foreach ( $array as $id => $group ) {
			$group['id']       = $id;
			$options->groups[] = Group::fromArray( $group );
		}

		return $options;
	}

	/**
	 * Return array configuration for checkout label setting field.
	 *
	 * Note: if you want to add an option in template to overwrite "Donate Now" button title then instead of define it manually in template options, developer can call this function.
	 * This function help to maintain backward compatibility with legacy donation form renderer.
	 *
	 * @return array
	 */
	public static function getCheckoutLabelField() {
		return [
			'id'                 => 'checkout_label',
			'name'               => __( 'Submit Button', 'give' ),
			'desc'               => __( 'The button label for completing a donation.', 'give' ),
			'type'               => 'text_medium',
			'attributes'         => [
				'placeholder' => __( 'Donate Now', 'give' ),
			],
			'default'            => __( 'Donate Now', 'give' ),
			'mapToLegacySetting' => '_give_checkout_label',
		];
	}

	/**
	 * Return array configuration for display style setting field.
	 *
	 * Note: if you want to add an option in template to overwrite donation levels style then instead of define it manually in template options, developer can call this function.
	 * This function help to maintain backward compatibility with legacy donation form renderer.
	 *
	 * @return array
	 */
	public static function getDonationLevelsDisplayStyleField() {
		return [
			'name'               => __( 'Display Style', 'give' ),
			'description'        => __( 'Set how the donations levels will display on the form.', 'give' ),
			'id'                 => 'display_style',
			'type'               => 'radio_inline',
			'default'            => 'buttons',
			'options'            => [
				'buttons'  => __( 'Buttons', 'give' ),
				'radios'   => __( 'Radios', 'give' ),
				'dropdown' => __( 'Dropdown', 'give' ),
			],
			'wrapper_class'      => 'give-hidden _give_display_style_field',
			'mapToLegacySetting' => '_give_checkout_label',
		];
	}
}
