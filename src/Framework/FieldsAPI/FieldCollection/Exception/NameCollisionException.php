<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Exception;

/**
 * @since 2.10.2
 */
class NameCollisionException extends \Exception {
	public function __construct( $name, $code = 0, Exception $previous = null ) {
		$message = "Node name collision for $name";
		parent::__construct( $message, $code, $previous );
	}
}
