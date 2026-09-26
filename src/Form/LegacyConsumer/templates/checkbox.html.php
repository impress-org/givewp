<?php
/** @since TBD Escape output. */
/** @var \Give\Framework\FieldsAPI\LegacyNodes\CheckboxGroup $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>

<?php
if ($field->hasOptions()): ?>
    <fieldset>
        <legend class="screen-reader-text">
            <?php
            include plugin_dir_path(__FILE__) . 'label-content.html.php'; ?>
        </legend>
        <div class="give-label" aria-hidden="true">
            <?php
            include plugin_dir_path(__FILE__) . 'label-content.html.php'; ?>
        </div>
        <?php
        foreach ($field->getOptions() as $index => $option) : ?>
            <?php
            $id = $fieldIdAttribute . '-' . $index; ?>
            <label class="give-label" for="<?php
            echo esc_attr($id); ?>">
                <input
                    type="checkbox"
                    name="<?php
                    echo esc_attr($field->getName()); ?>[]"
                    id="<?php
                    echo esc_attr($id); ?>"
                    <?php
                    echo in_array($option->getValue(), $field->getDefaultValue()) ? 'checked' : ''; ?>
                    value="<?php
                    echo esc_attr($option->getValue()); ?>"
                >
                <?php
                echo esc_html($option->getLabel() ?: $option->getValue()); ?>
            </label>
        <?php
        endforeach; ?>
    </fieldset>
<?php
else: ?>
    <label class="give-label">
        <input
            type="checkbox"
            name="<?php
            echo esc_attr($field->getName()); ?>"
            <?php
            echo $field->isRequired() ? 'required' : ''; ?>
            <?php
            echo $field->isChecked() ? 'checked' : ''; ?>
            <?php
            echo $field->isReadOnly() ? 'readonly' : ''; ?>
        >
        <?php
        echo esc_html($field->getLabel()); ?>
    </label>
<?php
endif; ?>
