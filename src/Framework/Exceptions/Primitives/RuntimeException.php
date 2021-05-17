<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Traits\Loggable;

class RuntimeException extends \RuntimeException {
	use Loggable;
}
