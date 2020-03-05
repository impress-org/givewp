<?php
namespace Give\Form\Themes;

use Give\Form\Theme;
use Give\Form\Themes;

/**
 * Load form themes
 *
 * Note: only for internal use. use give_form_themes filter to register new form theme.
 *
 * @since 2.7.0
 */
function load() {
	/**
	 * Filter list of form theme
	 *
	 * @since 2.7.0
	 */
	$themes = apply_filters(
		'give_form_themes',
		require GIVE_PLUGIN_DIR . 'src/Form/Config/Themes/Load.php'
	);

	foreach ( $themes as $theme ) {
		Give()->themes->set( new Theme( $theme ) );
	}
}
