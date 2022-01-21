<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Aliases {

	/**
	 * @var array
	 */
	protected $aliases = [];

	public function tableAliases( $aliases = [] ) {
        //TODO add some checks
		$this->aliases = $aliases;

        return $this;
	}

	public function alias( $table ) {
		if( isset( $this->aliases[ $table ] ) ) {
			return $this->aliases[ $table ];
		}
		return $table;
	}
}
