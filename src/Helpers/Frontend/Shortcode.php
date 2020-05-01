<?php

namespace Give\Helpers\Frontend;

class Shortcode {
	/**
	 * Get give_receipt shortcode with attributes from confirmation page.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public static function getReceiptShortcodeFromConfirmationPage() {
		$pattern = get_shortcode_regex();
		$post    = get_post( give_get_option( 'success_page', 0 ) );
		$content = $post->post_content;

		if (
			! empty( $content ) &&
			preg_match_all( '/' . $pattern . '/s', $content, $matches ) &&
			array_key_exists( 2, $matches ) &&
			in_array( 'give_receipt', $matches[2], true )
		) {
			return $matches[0][0];
		}

		return '';
	}
}
