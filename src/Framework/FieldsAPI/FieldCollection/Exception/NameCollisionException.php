<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Exception;

/**
 * @unreleased
 */
class NameCollisionException extends \Exception {
	public function __construct( $name, $code = 0, Exception $previous = null ) {
		$message = "Node name collision for $name";
		parent::__construct( $message, $code, $previous );
	}
}
