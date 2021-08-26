<?php

namespace Give\Form\LegacyConsumer\Validators;

use Give\Form\LegacyConsumer\Traits\HasFilesArray;
use Give\Framework\FieldsAPI\File;
use function _n;
use function esc_html__;
use function give_set_error;
use function size_format;

/**
 * @package Give\Form\LegacyConsumer\Validators
 * @unreleased
 */
class FileUploadValidator {
	use HasFilesArray;

	/**
	 * @var array
	 */
	private $files;
	/**
	 * @var File
	 */
	private $field;

	/**
	 * @unreleased
	 */
	public function __construct( File $field ) {
		$this->field = $field;
		$this->files = $this->getFiles();
	}
	/**
	 * @unreleased
	 */
	public function __invoke() {
		$uploadSize = 0;
		$fileTypes  = [];

		if( ! $this->files ) {
			if( $this->field->isRequired() ) {
				give_set_error(
					"give-{$this->field->getName()}-required-field-missing",
					$this->field->getRequiredError()['error_message']
				);
			}
			return;
		}

		foreach ( $this->files as $file ) {
			$uploadSize  += $file['size'];
			$fileTypes[] = $file['type'];
		}

		$uploadSize = (int) ceil( $uploadSize/1024 ); // bytes to kb
		$allowedFileTypes = $this->field->getAllowedTypes();
		$allowedFileSize = $this->field->getMaxSize();

		if ( array_diff( $fileTypes, $allowedFileTypes ) ) {
			give_set_error( 'field-api-file-upload-allowed-type-error', sprintf(
				esc_html__( 'Unable to upload file. Allowed file %1$s: %2$s', 'give' ),
				_n( 'type', 'types', count( $allowedFileTypes ), 'give' ),
				array_reduce(
					array_keys( $allowedFileTypes ),
					function ( $initial, $fileType ){
						$separator = $initial ? ', ' : '';
						$initial .= $separator . str_replace( '|', ', ', $fileType );

						return $initial;
					},
					''
				)
			) );
		}

		if ( $allowedFileSize < $uploadSize ) {
			give_set_error( 'field-api-file-upload-size-error', sprintf(
				esc_html__( 'File size exceed upload limit. Maximum file limit is %s', 'give' ),
				size_format( $allowedFileSize * 1024 )
			) );
		}
	}
}
