<?php

if (!defined('ABSPATH')) {
    exit;
}

/** @since 4.19.1 Escape output. */
/** @var Give\Framework\FieldsAPI\Field $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>
<label class="give-label" for="<?php
echo esc_attr($fieldIdAttribute); ?>">
    <?php
    include plugin_dir_path(__FILE__) . 'label-content.html.php'; ?>
</label>
