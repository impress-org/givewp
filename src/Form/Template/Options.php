<?php
namespace Give\Form\Template;

use Give\FormAPI\Group;

/**
 * Class Options
 *
 * @since 2.7.0
 * @package Give\Form\Template
 */
class Options {
	/**
	 * Theme Options
	 *
	 * @since 2.7.0
	 * @var array
	 */
	public $groups = [];

	/**
	 * ThemeOptions constructor.
	 *
	 * @since 2.7.0
	 * @param $array
	 *
	 * @return Options
	 */
	public static function fromArray( $array ) {
		$options = new static();

		foreach ( $array as $id => $group ) {
			$group['id']       = $id;
			$options->groups[] = Group::fromArray( $group );
		}

		return $options;
	}
}
