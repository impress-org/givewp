<?php /** @var Give\Framework\FieldsAPI\Hidden $field */ ?>
<input
	type="hidden"
	name="<?php echo $field->getName(); ?>"
	id="give-<?php echo $field->getName(); ?>"
	<?php if ( $value = $field->getDefaultValue() ) : ?>
	value="<?php echo esc_attr( $value ); ?>"
	<?php endif; ?>
/>
