<?php

namespace Give\Framework\FieldsAPI\Consumer;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Framework\FieldsAPI\Field\GroupNode;

abstract class AbstractConsumer {

    /** @var FieldCollection */
    protected $collection;

    public function __construct( FieldCollection $collection ) {
        $this->collection = $collection;
    }

    public function __invoke( $formID ) {
        $this->render();
    }

    public function render() {
        $this->renderCollection(
            $this->collection
        );
    }

    protected function renderCollection( FieldCollection $collection ) {
        foreach( $collection->getFields() as $node ) {
            if( $node instanceof GroupNode ) {
                $this->renderCollection( $node );
                continue;
            }
            $this->renderField( $node );
        }
    }

    protected abstract function renderField( FormField $field );
}
