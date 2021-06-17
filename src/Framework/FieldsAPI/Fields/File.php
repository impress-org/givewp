<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;
use Give\Framework\FieldsAPI\Fields\Contracts\ValidatesRequired;

/**
 * A file upload field.
 *
 * @unreleased
 */
class File implements Field, ValidatesRequired {

	// TODO: how would default values work for this and how would we serialize that? Do we want default values?
	//use Concerns\HasDefaultValue;

	use Concerns\AllowMultiple;
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;

	// TODO: Not sure how these would work
	//use Concerns\ShowInReceipt;
	//use Concerns\StoreAsMeta;

	/** @var string */
	protected $type = 'file';

	/** @var int */
	protected $maxSize = 1024;

	/** @var string[] */
	protected $allowedTypes = [ '*' ];

	/**
	 * Set the maximum file size.
	 *
	 * @param int $maxSize
	 * @return $this
	 */
	public function maxSize( $maxSize ) {
		$this->maxSize = $maxSize;
		return $this;
	}

	/**
	 * Access the maximum file size.
	 *
	 * @return int
	 */
	public function getMaxSize() {
		return $this->maxSize;
	}

	/**
	 * Set the allowed file types.
	 *
	 * @param string[] $allowedTypes
	 * @return $this
	 */
	public function allowedTypes( $allowedTypes = [ '*' ] ) {
		$this->allowedTypes = $allowedTypes;
		return $this;
	}

	/**
	 * Access the allowed file types.
	 *
	 * @return string[]
	 */
	public function getAllowedTypes() {
		return $this->allowedTypes;
	}
}
