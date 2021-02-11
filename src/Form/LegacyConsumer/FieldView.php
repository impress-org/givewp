<?php

namespace Give\Form\LegacyConsumer;

use Give\Framework\FieldsAPI\FormField;

class FieldView {
    public static function render( FormField $field ) {
        echo $field->getType();
        echo '<div class="form-row form-row-wide">';
            ob_start();
            include plugin_dir_path( __FILE__ ) . '/templates/label.html.php';
            include plugin_dir_path( __FILE__ ) . '/templates/' . $field->getType() . '.html.php';
            echo ob_get_clean();
        echo '</div>';
    }
}
