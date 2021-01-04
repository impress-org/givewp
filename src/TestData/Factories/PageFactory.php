<?php

namespace Give\TestData\Factories;

use Give\TestData\Framework\Factory;

/**
 * Class PageFactory
 * @package Give\TestData\Factories
 */
class PageFactory extends Factory {

	/**
	 * Donor definition
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function definition() {
		return [
			'post_title'   => 'GiveWP Demonstration page',
			'post_content' => $this->getContent(),
			'post_status'  => 'publish',
			'post_author'  => $this->randomAuthor(),
			'post_type'    => 'page',
		];
	}

	/**
	 * Page content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getContent() {

		$giveShortcodes = $this->getGiveShortcodes();

		$content = '';

		foreach ( $giveShortcodes as $shortcode ) {
			$content .= "<h3>[{$shortcode}]</h3>";
			$content .= "[{$shortcode}]";
		}

		return $content;

	}

	/**
	 * Get GiveWP shortcodes
	 *
	 * @return array
	 */
	private function getGiveShortcodes() {

		$shortcodes = [];

		foreach ( $GLOBALS['shortcode_tags'] as $shortcode => $action ) {
			if ( false !== strpos( $shortcode, 'give_' ) ) {
				$shortcodes[] = $shortcode;
			}
		}

		return $shortcodes;
	}
}
