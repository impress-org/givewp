<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Traits\Loggable;

class InvalidArgumentException extends \InvalidArgumentException {
	use Loggable;
}
