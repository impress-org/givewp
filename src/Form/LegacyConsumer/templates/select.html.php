<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<select
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
  class="give-input required"
	<?php if ( $field->isRequired() ) : ?>
	  required
	<?php endif; ?>
  @attributes
>
	<?php foreach ( $field->getOptions() as $key => $value ) : ?>
	  <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
