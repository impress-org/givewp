<?php

namespace Give\Form\LegacyConsumer\Traits;

use Give\Framework\FieldsAPI\File;

/**
 * @since 2.14.0
 *
 * @property File $field
 */
trait HasFilesArray {
	/**
	 * @since 2.14.0
	 * @return array
	 */
	public function getFiles(){
		$_files = $_FILES[ $this->field->getName() ];
		$files = [];

		if( ! $this->field->getAllowMultiple() ) {
			return [ $_files ];
		}

		foreach ( $_files as $key => $data ) {
			foreach ( $data as $index => $item ) {
				$files[$index][$key] = $item;
			}
		}

		return array_filter( $files, function( $file ){
			return empty( $file['error' ] );
		});
	}
}
