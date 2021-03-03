<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

class FormField implements Node {

	use FormField\FieldLabel;
	use FormField\FieldOptions;
	use FormField\FieldRequired;
	use FormField\FieldReadOnly;
	use FormField\FieldHelpText;
	use FormField\FieldDefaultValue;
	use FormField\FieldAttributes;
	use FormField\FieldEmailTag;
	use FormField\FieldStoreAsMeta;
	use FormField\FieldReceipt;

	/** @var string */
	protected $type;

	/** @var string */
	protected $name;

	public function __construct( $type, $name ) {
		$this->type = $type;
		$this->name = $name;
	}

	public function getType() {
		return $this->type;
	}

	public function getName() {
		return $this->name;
	}

	public function jsonserialize() {
		return [
			'type'     => $this->getType(),
			'name'     => $this->getName(),
			'required' => $this->isRequired(),
			'readOnly' => $this->isReadOnly(),
			'options'  => $this->getOptions(),
		];
	}
}
