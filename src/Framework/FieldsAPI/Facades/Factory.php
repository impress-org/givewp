<?php

namespace Give\Framework\FieldsAPI\Facades;

use Give\Framework\Support\Facades\Facade;

class Factory extends Facade {
	protected function getFacadeAccessor() {
		return \Give\Framework\FieldsAPI\Factory::class;
	}
}
