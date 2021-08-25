<?php

namespace Give\Form\LegacyConsumer\Traits;

/**
 * @unreleased
 *
 * @property array $files
 */
trait RemoveInvalidFiles {
	/**
	 * @unreleased
	 */
	protected function removeInvalidFiles(){
		foreach ( $this->files as $index => $file ){
			if( ! empty( $file['error'] ) ) {
				unset( $this->files[$index] );
			}
		}
	}
}
