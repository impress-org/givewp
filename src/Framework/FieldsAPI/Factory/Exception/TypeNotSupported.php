<?php

namespace Give\Framework\FieldsAPI\Factory\Exception;

use Exception;
use Give\Framework\Exceptions\Traits\Loggable;

class TypeNotSupported extends Exception {
	use Loggable;

	public function __construct( $type, $code = 0, $previous = null ) {
		$message = "Field type $type is not supported";
		parent::__construct( $message, $code, $previous );
	}
}
