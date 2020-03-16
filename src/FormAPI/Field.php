<?php
namespace Give\FormAPI;

use InvalidArgumentException;

class Field {

	/**
	 * Field id
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $id;

	/**
	 * Field name
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $name;

	/**
	 * Field description
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $desc;

	/**
	 * Field type
	 *
	 * @since 2.7.0
	 * @var string
	 */
	public $type;

	/**
	 * Field arguments
	 *
	 * @since 2.7.0
	 * @var array
	 */
	private $array;


	/**
	 * @param array $array
	 *
	 * @return static
	 */
	public static function fromArray( $array ) {
		$field = new static();

		$field->validate( $array );

		$field->id   = $array['id'];
		$field->name = $array['name'];
		$field->type = $array['type'];
		$field->desc = isset( $array['desc'] ) ? $array['desc'] : null;

		$field->array = $array;

		return $field;
	}


	/**
	 * Validate field arguments
	 *
	 * @since 2.7.0
	 * @param $array
	 */
	private function validate( $array ) {
		$required = array( 'id', 'name', 'type' );

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a field object, please provide id, name and type.', 'give' ) );
		}
	}


	/**
	 * Get form metabox api compatible arguments
	 * Note: for internal use only nd can be remove in future with any backward compatibility.
	 *
	 * @since 2.7.0
	 * @return array
	 */
	public function getFormMetaboxFieldArguments() {
		return $this->array;
	}
}
