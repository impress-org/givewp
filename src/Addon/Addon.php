<?php
namespace Give\Addon;

interface Addon {
	/**
	 * Return whether or not addon active.
	 *
	 * @since 2.7.0
	 * @return bool
	 */
	public static function isActive();
}
