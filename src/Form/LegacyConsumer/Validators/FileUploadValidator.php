<?php

namespace Give\Form\LegacyConsumer\Validators;

use Give\Framework\FieldsAPI\File;

/**
 * @package Give\Form\LegacyConsumer\Validators
 * @unreleased
 */
class FileUploadValidator {
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
		$this->files = $_FILES;
		$this->field = $field;
	}
	/**
	 * @unreleased
	 */
	public function __invoke() {
		$uploadSize = 0;
		$fileTypes  = [];

		if( ! $this->files ) {
			return;
		}

		foreach ( $this->files as $file ) {
			$uploadSize  += $file['size'];
			$fileTypes[] = $file['type'];
		}

		$uploadSize = $uploadSize/1024; // bytes to kb
		$allowedFileTypes = $this->field->getAllowedTypes();
		$allowedFileSize = $this->field->getMaxSize();

		if ( ! in_array( '*', $allowedFileTypes ) || array_diff( $fileTypes, $allowedFileTypes ) ) {
			give_set_error( 'field-api-file-upload-allowed-type-error', sprintf(
				esc_html__( 'Unable to upload file. Allowed file types %s', 'give' ),
				implode( ', ', $this->field->getAllowedTypes() )
			) );
		}

		if ( $allowedFileSize < $uploadSize ) {
			give_set_error( 'field-api-file-upload-size-error', sprintf(
				esc_html__( 'File size exceed upload limit. Maximum file limit is %s kb', 'give' ),
				size_format( $allowedFileSize * 1024 )
			) );
		}
	}
}
