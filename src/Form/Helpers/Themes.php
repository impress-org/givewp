<?php
namespace Give\Form\Themes;

use Give\Form\Theme;
use Give\Form\Themes;

/**
 * Register core themes
 *
 * @since 2.7.0
 */
function registerDefaults() {
	/**
	 * Register themes
	 */
	$themes = require GIVE_PLUGIN_DIR . 'src/Form/Config/Themes/Load.php';

	foreach ( $themes as $theme ) {
		Themes::set( new Theme( $theme ) );
	}
}
