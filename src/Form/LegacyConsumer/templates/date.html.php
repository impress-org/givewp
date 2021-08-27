<?php /** @var Give\Framework\FieldsAPI\Date $field */ ?>
<?php /** @var string $typeAttribute */ ?>
<input
	type="text"
	name="<?php echo $field->getName(); ?>"
	placeholder="<?php echo $field->getPlaceholder(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	value="<?php echo $field->getDefaultValue(); ?>"
	data-timeformat="<?php echo $field->getDateFormat(); ?>"
	data-dateformat="<?php echo $field->getTimeFormat(); ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
