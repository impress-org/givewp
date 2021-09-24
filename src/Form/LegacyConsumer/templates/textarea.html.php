<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<textarea
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo ( $maxLength = $field->getMaxLength() ) ? "maxlength=\"$maxLength\"" : ''; ?>
	<?php
	if ( $conditions = $field->getVisibilityConditions() ) {
		$conditions = esc_attr( json_encode( $conditions ) );
		echo "data-field-visibility-conditions=\"$conditions\"";
	}
	?>
></textarea>
