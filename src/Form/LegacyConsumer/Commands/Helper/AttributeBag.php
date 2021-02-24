<?php

namespace Give\Form\LegacyConsumer\Commands\Helper;

class AttributeBag {
	public function __construct( $attributes = [] ) {
		foreach ( $attributes as $key => $value ) {
			$this->$key = $value;
		}
	}
}
