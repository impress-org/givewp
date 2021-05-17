<?php

namespace Give\FormAPI;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * Class Options
 *
 * @since   2.7.0
 * @package Give\Form\Theme
 */
class Section {

	/**
	 * Group id.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $id;

	/**
	 * Name of group
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $name;


	/**
	 * Description og group.
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $desc;


	/**
	 * Array fo fields
	 *
	 * @since 2.7.0
	 * @var Fields[]
	 */
	public $fields = [];


	/**
	 * Convert array into Group class object.
	 *
	 * @param array $array
	 *
	 * @since 2.7.0
	 *
	 * @return static
	 */
	public static function fromArray( $array ) {
		$group = new static();

		$group->validate( $array );

		$group->id   = $array['id'];
		$group->name = $array['name'];
		$group->desc = isset( $array['desc'] ) ? $array['desc'] : null;

		foreach ( $array['fields'] as $field ) {
			$group->fields[] = Fields::fromArray( $field );
		}

		return $group;
	}

	/**
	 * Validate group arguments
	 *
	 * @since 2.7.0
	 * @param $array
	 */
	private function validate( $array ) {
		$required = [ 'id', 'name', 'fields' ];

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a Group object, please provide id, name and fields.', 'give' ) );
		}
	}
}
