<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Traits\Loggable;

class HttpHeaderException extends \HttpHeaderException {
	use Loggable;
}
