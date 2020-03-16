<?php
namespace Give\Form\Theme;

use Give\Form\Theme;
use WP_Post;
use function Give\Helpers\Form\Theme\get as getTheme;

/**
 * Class Options
 *
 * @since 2.7.0
 * @package Give\Form\Theme
 */
class Options {
	/**
	 * @since 2.7.0
	 * @var Theme $theme
	 */
	private $theme;

	/**
	 * ThemeOptions constructor.
	 *
	 * @since 2.7.0
	 * @param Theme $theme
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
	}

	/**
	 * return theme options.
	 *
	 * @since 2.7.0
	 *
	 * @global WP_Post $post
	 * @return string
	 */
	public function render() {
		global $post;

		ob_start();

		$saveOptions = getTheme( $post->ID, $this->theme->getID() );

		foreach ( $this->theme->getOptionsConfig() as $groupID => $option ) {
			printf(
				'<div class="give-row %1$s">',
				$groupID
			);

			printf(
				'<div class="give-row-head">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="toggle-indicator"/>
							</button>
							<h2 class="hndle"><span>%1$s</span></h2>
						</div>',
				$option['name']
			);

			echo '<div class="give-row-body">';
			foreach ( $option['fields'] as $field ) {
				if ( isset( $saveOptions[ $groupID ][ $field['id'] ] ) ) {
					$field['attributes']['value'] = $saveOptions[ $groupID ][ $field['id'] ];
				}

				$field['id'] = "{$this->theme->getID()}[{$groupID}][{$field['id']}]";

				give_render_field( $field );
			}

			echo '</div></div>';
		}

		return ob_get_clean();
	}
}
