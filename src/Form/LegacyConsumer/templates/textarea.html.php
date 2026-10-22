<?php
/** @since TBD Escape output. */
/** @var Give\Framework\FieldsAPI\Textarea $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>

<textarea
    name="<?php
    echo esc_attr($field->getName()); ?>"
    id="<?php
    echo esc_attr($fieldIdAttribute); ?>"
	<?php
    echo $field->isRequired() ? 'required' : ''; ?>
    <?php
    echo $field->isReadOnly() ? 'readonly' : ''; ?>
    <?php
    echo ($maxLength = $field->getMaxLength()) ? 'maxlength="' . (int) $maxLength . '"' : ''; ?>
>
<?php
echo esc_textarea($field->getDefaultValue()); /* Whitespace is important. Do not indent. */ ?>
</textarea>
