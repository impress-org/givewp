<?php /** @var Give\Framework\FieldsAPI\Checkbox $field */ ?>
<?php /** @var string $typeAttribute */ ?>
<label class="give-label" for="give-<?php echo $field->getName(); ?>">
	<?php echo $field->getLabel(); ?></label>
<input
	type="checkbox"
	id="give-<?php echo $field->getName(); ?>"
	name="<?php echo $field->getName(); ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isChecked() ? 'checked' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
