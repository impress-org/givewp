<?php /** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php /** @var string $classAttribute */ ?>
<textarea
  class="<?php echo $classAttribute; ?>"
  name="give_<?php echo $field->getName(); ?>"
  id="give-<?php echo $field->getName(); ?>"
	<?php if ( $field->isRequired() ) : ?>
	required
  <?php endif; ?>
></textarea>
