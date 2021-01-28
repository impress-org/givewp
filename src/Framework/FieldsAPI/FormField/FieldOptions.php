<?php

namespace Give\Framework\FieldsAPI\FormField;

use Give\Framework\FieldsAPI\FormField\FieldTypes;

trait FieldOptions {

    /** @var array */
    protected $options = [];

    public function supportsOptions() {
        return in_array( $this->getType(), [
            FieldTypes::TYPE_SELECT,
            FieldTypes::TYPE_RADIO,
        ] );
    }

    public function addOptions( $options ) {
        $this->options = array_merge( $this->options, $options );
    }

    public function addOption( $key, $value ) {
        array_push( $this->options, [ $key => $value ] );
    }

    public function getOptions() {
        return $this->options;
    }
}