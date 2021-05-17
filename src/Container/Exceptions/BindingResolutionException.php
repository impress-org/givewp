<?php

namespace Give\Container\Exceptions;

use Exception;
use Give\Framework\Exceptions\Traits\Loggable;

class BindingResolutionException extends Exception {
	use Loggable;
}
