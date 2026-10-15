<?php

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables; this file is included inside a function, so they are not globals.

/** @since TBD Escape output. */
/** @var Give\Framework\FieldsAPI\Hidden $field */ ?>
<?php
/** @var string $fieldIdAttribute */ ?>
<input
    type="hidden"
    name="<?php
    echo esc_attr($field->getName()); ?>"
    id="<?php
    echo esc_attr($fieldIdAttribute); ?>"
    <?php
    if ($value = $field->getDefaultValue()) : ?>
        value="<?php
        echo esc_attr($value); ?>"
    <?php
    endif; ?>
/>
