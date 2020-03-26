<?php

namespace Give\Helpers\Frontend;

/**
 * Get give_receipt shortcode with attributes from confirmation page.
 *
 * @param string $shortcode Shortcode Id.
 *
 * @return string
 * @since 2.7.0
 */
function getReceiptShortcodeFromConfirmationPage( $shortcode ) {
	$pattern = get_shortcode_regex();
	$post    = get_post( give_get_option( 'success_page', 0 ) );
	$content = $post->post_content;

	if (
		! empty( $content ) &&
		preg_match_all( '/' . $pattern . '/s', $content, $matches ) &&
		array_key_exists( 2, $matches ) &&
		in_array( $shortcode, $matches[2], true )
	) {
		return $matches[0][0];
	}

	return '';
}
