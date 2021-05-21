<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * Class InvalidPropertyName
 * @package Give\Framework\Exceptions\Primitives
 * @unreleased
 */
class InvalidPropertyName extends \RuntimeException implements LoggableException {
	use Loggable;
}
