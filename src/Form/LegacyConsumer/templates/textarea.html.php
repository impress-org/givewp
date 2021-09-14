<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<textarea
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php echo $field->isRequired() ? 'required' : ''; ?>
	<?php echo $field->isReadOnly() ? 'readonly' : ''; ?>
	<?php echo ( $maxLength = $field->getMaxLength() ) ? "maxlength=\"$maxLength\"" : ''; ?>
	<?php echo include dirname( __FILE__ ) . '/conditional-visibility-attribute.html.php'; ?>
></textarea>
