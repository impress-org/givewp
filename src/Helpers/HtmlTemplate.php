<?php

namespace Give\Helpers;

use stdClass;

/**
 * Class HtmlTemplate
 * @package Give\Helpers
 *
 * @since 2.8.0
 */
class HtmlTemplate {
	/**
	 * Return loader overlay.
	 *
	 * @since 2.8.0
	 *
	 * @param  stdClass  $config
	 *
	 * @return string
	 */
	public static function LoaderOverlay( $config ) {
		$config = wp_parse_args(
			$config,
			[ 'heading' => esc_html__( 'Processing...', 'give' ) ]
		);

		return <<<EOT
			<div id="give-processing-state-template">
				<div>
					<span style="color:#000; font-size: 26px; margin:0 0 0 10px;">{$config['heading']}</span>
				</div>
			</div>
EOT;

	}
}
