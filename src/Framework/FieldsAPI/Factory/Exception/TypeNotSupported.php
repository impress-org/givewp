<?php

namespace Give\Framework\FieldsAPI\Factory\Exception;

use Exception;

class TypeNotSupported extends Exception {
	public function __construct( $type, $code = 0 ) {
		$message = "Field type $type is not supported";
		parent::__construct( $message, $code );
	}
}
