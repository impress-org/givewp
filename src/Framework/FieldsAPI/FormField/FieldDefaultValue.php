<?php

namespace Give\Framework\FieldsAPI\FormField;

trait FieldDefaultValue {

    /** @var string */
    public $defaultValue;

    /**
     * @param string $defaultValue
     * @return $this
     */
    public function default( $defaultValue ) {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }
}