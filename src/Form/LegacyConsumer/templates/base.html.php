<?php
/** @var \Give\Framework\FieldsAPI\Text $field */ ?>
<?php
/** @var string $typeAttribute */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>
<input
    type="<?php
    echo $typeAttribute; ?>"
    name="<?php
    echo $field->getName(); ?>"
    placeholder="<?php
    echo $field->getPlaceholder(); ?>"
    id="<?php
    echo $fieldIdAttribute; ?>"
    value="<?php
    echo $field->getDefaultValue(); ?>"
    <?php
    echo $field->isRequired() ? 'required' : ''; ?>
    <?php
    echo $field->isReadOnly() ? 'readonly' : ''; ?>
    <?php
    echo ($maxLength = $field->getMaxLength()) ? "maxlength=\"$maxLength\"" : ''; ?>
>
