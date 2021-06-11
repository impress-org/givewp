<?php /** @var Give\Framework\FieldsAPI\FormField $field */ ?>
<?php /** @var string $classAttribute */ ?>
<textarea
  class="<?php echo $classAttribute; ?>"
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	required
  <?php endif; ?>
	@attributes
></textarea>
