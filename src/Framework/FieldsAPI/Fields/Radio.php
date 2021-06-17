<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

class Radio implements Field {

	use Concerns\CreatesSelfWithName;
	use Concerns\HasDefaultValue;
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasName;
	use Concerns\HasOptions;
	use Concerns\HasPlaceholder;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	/** @var string */
	protected $type = 'radio';

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return [
			'type'        => $this->getType(),
			'name'        => $this->getName(),
			'label'       => $this->getLabel(),
			'helpText'    => $this->getHelpText(),
			'placeholder' => $this->getPlaceholder(),
			'options'     => array_map(
				function ( $option ) {
					return $option->jsonSerialize();
				},
				$this->getOptions()
			),
			'readOnly'    => $this->isReadOnly,
		];
	}
}
