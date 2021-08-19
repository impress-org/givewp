<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<label>
	<?php echo $field->getLabel(); ?>
	<textarea name="give_<?php echo $field->getName(); ?>"<?php echo $field->isRequired() ? ' required' : ''; ?>></textarea>
</label>
