<?php
/**
 * Form helper functions
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

/**
 * Register form theme
 *
 * @since 2.7.0
 * @param array $data
 */
function registerTheme( $data ) {
	Themes::store( ( new Theme( $data ) ) );
}
