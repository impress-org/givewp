<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Theme\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
abstract class Theme {
	/**
	 * template vs class array
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $templates = [
		'receipt' => GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormTemplate.php',
		'form'    => GIVE_PLUGIN_DIR . 'src/Views/Form/defaultFormReceiptTemplate.php',
	];

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
	 * @param string $template
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getTemplate( $template ) {
		return $this->templates[ $template ];
	}


	/**
	 * Get theme options
	 *
	 * @return Options
	 */
	public function getOptions() {
		return Options::fromArray( $this->getOptionsConfig() );
	}
}
