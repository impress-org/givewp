<?php

if ( ! function_exists( 'get_comment_delimited_block_content' ) ) {
	/**
	 * Returns the content of a block, including comment delimiters.
	 *
	 * @since 5.3.1
	 *
	 * @param string|null $block_name Block name. Null if the block name is unknown,
	 *                                      e.g. Classic blocks have their name set to null.
	 * @param array       $block_attributes Block attributes.
	 * @param string      $block_content Block save content.
	 *
	 * @return string Comment-delimited block content.
	 */
	function get_comment_delimited_block_content( $block_name, $block_attributes, $block_content ) {
		if ( is_null( $block_name ) ) {
			return $block_content;
		}

		$serialized_block_name = strip_core_block_namespace( $block_name );
		$serialized_attributes = empty( $block_attributes ) ? '' : serialize_block_attributes( $block_attributes ) . ' ';

		if ( empty( $block_content ) ) {
			return sprintf( '<!-- wp:%s %s/-->', $serialized_block_name, $serialized_attributes );
		}

		return sprintf(
			'<!-- wp:%s %s-->%s<!-- /wp:%s -->',
			$serialized_block_name,
			$serialized_attributes,
			$block_content,
			$serialized_block_name
		);
	}
}

if ( ! function_exists( 'strip_core_block_namespace' ) ) {
	/**
	 * Returns the block name to use for serialization. This will remove the default
	 * "core/" namespace from a block name.
	 *
	 * @since 5.3.1
	 *
	 * @param string $block_name Original block name.
	 *
	 * @return string Block name to use for serialization.
	 */
	function strip_core_block_namespace( $block_name = null ) {
		if ( is_string( $block_name ) && 0 === strpos( $block_name, 'core/' ) ) {
			return substr( $block_name, 5 );
		}

		return $block_name;
	}
}

if ( ! function_exists( 'serialize_block_attributes' ) ) {
	/**
	 * Given an array of attributes, returns a string in the serialized attributes
	 * format prepared for post content.
	 *
	 * The serialized result is a JSON-encoded string, with unicode escape sequence
	 * substitution for characters which might otherwise interfere with embedding
	 * the result in an HTML comment.
	 *
	 * @since 5.3.1
	 *
	 * @param array $block_attributes Attributes object.
	 *
	 * @return string Serialized attributes.
	 */
	function serialize_block_attributes( $block_attributes ) {
		$encoded_attributes = json_encode( $block_attributes );
		$encoded_attributes = preg_replace( '/--/', '\\u002d\\u002d', $encoded_attributes );
		$encoded_attributes = preg_replace( '/</', '\\u003c', $encoded_attributes );
		$encoded_attributes = preg_replace( '/>/', '\\u003e', $encoded_attributes );
		$encoded_attributes = preg_replace( '/&/', '\\u0026', $encoded_attributes );
		// Regex: /\\"/
		$encoded_attributes = preg_replace( '/\\\\"/', '\\u0022', $encoded_attributes );

		return $encoded_attributes;
	}
}
