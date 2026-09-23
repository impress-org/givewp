<?php
/** @since TBD Escape output. */
/** @var Give\Framework\FieldsAPI\Radio $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>
<?php
/* Fieldsets + legends are terrible to style, so we just use the semantic markup and style something else. */ ?>
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
        <label for="<?php
        echo esc_attr($id); ?>">
            <input
                type="radio"
                name="<?php
                echo esc_attr($field->getName()); ?>"
                id="<?php
                echo esc_attr($id); ?>"
                <?php
                echo $field->isRequired() ? 'required' : ''; ?>
                <?php
                echo $option->getValue() === $field->getDefaultValue() ? 'checked' : ''; ?>
                value="<?php
                echo esc_attr($option->getValue()); ?>"
            >
            <?php
            echo esc_html($option->getLabel() ?: $option->getValue()); ?>
        </label>
    <?php
    endforeach; ?>
</fieldset>
