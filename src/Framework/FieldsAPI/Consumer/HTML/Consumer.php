<?php

namespace Give\Framework\FieldsAPI\Consumer\HTML;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\Consumer\AbstractConsumer;

class Consumer extends AbstractConsumer {
    protected function renderField( FormField $field ) {
        ?>
        <p class="form-row form-row-wide">
            <?php include 'templates/label.html.php'; ?>

            <?php if( 'text' == $field->getType() ): ?>
                <?php include 'templates/text.html.php'; ?>
            <?php endif; ?>

            <?php if( 'textarea' == $field->getType() ): ?>
                <?php include 'templates/textarea.html.php'; ?>
            <?php endif; ?>
        </p>
        <?php
    }
}