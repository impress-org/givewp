<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
	type="hidden"
	id="give-<?php echo $field->getName(); ?>"
	name="<?php echo $field->getName(); ?>"
	value="<?php echo $field->getDefaultValue(); ?>"
>
