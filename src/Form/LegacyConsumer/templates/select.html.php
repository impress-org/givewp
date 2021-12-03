<?php
/** @var Give\Framework\FieldsAPI\Select $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>

<select
    name="<?php
    echo $field->getName(); ?><?php
    echo $field->getAllowMultiple() ? '[]' : ''; ?>"
    id="<?php
    echo $fieldIdAttribute; ?>"
    <?php
    echo $field->getAllowMultiple() ? 'multiple' : ''; ?>
    <?php
    echo $field->isRequired() ? 'required' : ''; ?>
    <?php
    echo $field->isReadOnly() ? 'readonly' : ''; ?>
>
    <?php
    if ($placeholder = $field->getPlaceholder()) {
        printf(
            '<option value="" %2$s %3$s>%1$s</option>',
            $placeholder,
            $field->isRequired() ? 'disabled' : '',
            $field->getDefaultValue() ? '' : 'selected'
        );
    }
    ?>
    <?php
    foreach ($field->getOptions() as $option) : ?>
        <?php
        $value = esc_attr($option->getValue());
        $label = $option->getLabel();
        $default = $field->getAllowMultiple() ?
            in_array($option->getValue(), $field->getDefaultValue()) :
            $field->getDefaultValue() === $option->getValue();
        ?>
        <option
            <?php
            echo $label ? "value=\"$value\"" : ''; ?>
            <?php
            echo $default ? 'selected' : ''; ?>
        >
            <?php
            echo $label ?: $value; ?>
        </option>
    <?php
    endforeach; ?>
</select>
