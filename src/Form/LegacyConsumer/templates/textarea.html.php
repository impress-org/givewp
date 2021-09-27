<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<?php /** @var string $visibilityConditionsAttribute */ ?>

<textarea
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo ( $maxLength = $field->getMaxLength() ) ? "maxlength=\"$maxLength\"" : ''; ?>
	<?php echo $visibilityConditionsAttribute ?>
></textarea>
