<?php /** @var Give\Framework\FieldsAPI\Hidden $field */ ?>
<?php /** @var string $fieldIdAttribute */ ?>
<input
	type="hidden"
	name="<?php echo $field->getName(); ?>"
	id="<?php echo $fieldIdAttribute; ?>"
	<?php if ( $value = $field->getDefaultValue() ) : ?>
	value="<?php echo esc_attr( $value ); ?>"
	<?php endif; ?>
/>
