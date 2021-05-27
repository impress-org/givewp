<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<input
  type="text"
  class="give-input required"
  name="<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	  required
	<?php endif; ?>
  @attributes
/>
