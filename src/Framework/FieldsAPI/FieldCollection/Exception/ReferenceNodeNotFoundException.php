<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Exception;

/**
 * @since 2.10.2
 */
class ReferenceNodeNotFoundException extends \Exception {
	public function __construct( $name, $code = 0, Exception $previous = null ) {
		$message = "Reference node with the name \"$name\" not found - cannot insert new node.";
		parent::__construct( $message, $code, $previous );
	}
}
