<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Traits\Loggable;

class Exception extends \Exception {
	use Loggable;
}
