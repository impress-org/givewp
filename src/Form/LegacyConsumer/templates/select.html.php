<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string $classAttribute */ ?>
<select
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
  class="<?php echo $classAttribute; ?>"
	<?php if ( $field->isRequired() ) : ?>
	  required
	<?php endif; ?>
  @attributes
>
	<?php foreach ( $field->getOptions() as $option ) : ?>
		<?php if ( $label = $option->getLabel() ) : ?>
	<option value="<?php echo $option->getValue(); ?>"><?php echo $label; ?></option>
		<?php else : ?>
	<option><?php echo $option->getValue(); ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
