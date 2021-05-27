<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<textarea
  class="give-input required"
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	required
  <?php endif; ?>
	@attributes
></textarea>
