<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<textarea
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	required
  <?php endif; ?>
></textarea>
