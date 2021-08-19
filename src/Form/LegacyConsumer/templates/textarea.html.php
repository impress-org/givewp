<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<textarea name="give_<?php echo $field->getName(); ?>"<?php echo $field->isRequired()? ' required' : ''; ?>></textarea>
