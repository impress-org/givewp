<?php
namespace Give\FormAPI;

use InvalidArgumentException;

class Field {
	/**
	 * Get field object.
	 *
	 * @since 2.7.0
	 * @param array $array
	 *
	 * @return Form\Field
	 */
	public static function fromArray( $array ) {
		$field = new static();
		$field->validate( $array );

		return $field->parse( $array );
	}


	/**
	 * Parse field argument and return object
	 *
	 * @since 2.7.0
	 * @param array $array
	 * @return Form\Field
	 */
	private function parse( $array ) {
		/**
		 * Field type can contain multiple words join with underscore.
		 * First word will give actual type for field and other will be modifier.
		 * For example text_small where text is actual field type and small is a modifier.
		 */
		$type = $array['type'];

		/* @var Form\Field $class */
		$class = 'Give\FormAPI\Form\\' . $this->getFieldClassName( $type );

		if ( ! class_exists( $class ) ) {
			throw new InvalidArgumentException( __( "{$type} field type is not supported by field API.", 'give' ) );
		}

		return $class::fromArray( $array );
	}

	/**
	 * Get field class name.
	 *
	 * @since 2.7.0
	 * @param $type
	 *
	 * @return string
	 */
	private function getFieldClassName( $type ) {
		$type = ucwords( $type );

		if ( false !== strpos( $type, '_' ) ) {
			$type = current( explode( '_', $type, 2 ) );
		}

		return $type;
	}

	/**
	 * Validate field arguments
	 *
	 * @since 2.7.0
	 *
	 * @param array $array
	 *
	 * @throws InvalidArgumentException
	 */
	private function validate( $array ) {
		$required = array( 'id', 'name', 'type' );
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException( __( 'To create a TextField object, please provide valid id, name and type.', 'give' ) );
		}
	}
}
