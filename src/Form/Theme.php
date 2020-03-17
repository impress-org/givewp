<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Theme\Options;
use Give\FormAPI\Form\Field;
use Give\FormAPI\Group;
use WP_Post;
use function Give\Helpers\Form\Theme\get as getTheme;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
abstract class Theme {
	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract  public function getID();

	/**
	 * Get theme name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get theme image.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getImage();

	/**
	 * Get options config
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	abstract public function getOptionsConfig();


	/**
	 * Theme template manager get template according to view.
	 * Note: Do not forget to call this function before close bracket in overridden getTemplate method
	 *
	 * public function getTemplate( $template ) {
	 *     switch ( $template ) {
	 *        case 'receipt':
	 *           return __DIR__ . '/receipt.php';
	 *
	 *     }
	 *
	 *     return parent::getTemplate( $template );
	 * }
	 *
	 * @param string $template
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getTemplate( $template ) {
		switch ( $template ) {
			case 'donationForm':
				return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormTemplate.php';

			case 'receipt':
				return GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormReceiptTemplate.php';
		}
	}


	/**
	 * Get theme options
	 *
	 * @return Options
	 */
	final public function getOptions() {
		return Options::fromArray( $this->getOptionsConfig() );
	}

	/**
	 * return theme options.
	 *
	 * @since 2.7.0
	 *
	 * @global WP_Post $post
	 * @return string
	 */
	final public function render() {
		global $post;

		ob_start();

		$saveOptions = getTheme( $post->ID, $this->getID() );

		/* @var Group $option */
		foreach ( $this->getOptions()->groups as $group ) {
			printf(
				'<div class="give-row %1$s">',
				$group->id
			);

			printf(
				'<div class="give-row-head">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator"/>
							</button>
							<h2 class="hndle"><span>%1$s</span></h2>
						</div>',
				$group->name
			);

			echo '<div class="give-row-body">';

			/* @var Field $field */
			foreach ( $group->fields as $field ) {
				$field = $field->toArray();
				if ( isset( $saveOptions[ $group->id ][ $field['id'] ] ) ) {
					$field['attributes']['value'] = $saveOptions[ $group->id ][ $field['id'] ];
				}

				$field['id'] = "{$this->getID()}[{$group->id}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}

}
