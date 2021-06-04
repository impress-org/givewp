<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
	type="hidden"
	id="give-<?= $field->getName() ?>"
	name="<?= $field->getName() ?>"
	value="<?= $field->getDefaultValue() ?>"
>
