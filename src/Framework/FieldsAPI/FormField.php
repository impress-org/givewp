<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\FieldCollection\Contract\FieldNode;

class FormField implements FieldNode {

    use FormField\FieldLabel;
    use FormField\FieldRequired;
    use FormField\FieldReadOnly;

    /** @var string */
    protected $type;

    /** @var string */
    protected $name;

    public function __construct( $type, $name ) {
        $this->type = $type;
        $this->name = $name;
    }

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function jsonserialize() {
        return [
            'type' => $this->getType(),
            'name' => $this->getName(),
            // 'required' => $this->isRequired(),
            // 'readOnly' => $this->isReadOnly(),
        ];
    }
}