<?php

namespace Give\Framework\FieldsAPI\Concerns;

use JsonSerializable;

trait SerializeAsJson
{

    /**
     * {@inheritdoc}
     *
     * @since 2.22.0 add nodeTye to the json
     */
    public function jsonSerialize(): array
    {
        return array_merge(
        // These values must be serialized for all types
            [
                'nodeType' => $this->getNodeType(),
                'type' => $this->getType(),
                'name' => $this->getName(),
            ],
            // We (recursively) serialize all of the class’ properties and exclude the list provided.
            array_map(
                static function ($value) {
                    if ($value instanceof JsonSerializable) {
                        return $value->jsonSerialize();
                    }

                    return $value;
                },
                get_object_vars($this)
            )
        );
    }
}
