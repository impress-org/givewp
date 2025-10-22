<?php

namespace Give\API\REST\V3\Support\Schema;

use Give\API\REST\V3\Support\Schema\SchemaType;

/**
 * @unreleased
 */
class SchemaTypes
{

    /**
     * This is used to define a money schema for fields that use the Give\Framework\Support\ValueObjects class.
     *
     * @unreleased
     */
    public static function money(): SchemaType
    {
        $moneySchemaType = new SchemaType();
        $moneySchemaType->type('object')
        ->properties([
            'value' => [
                'type' => 'number',
                'description' => esc_html__('Value in decimal format', 'give'),
            ],
            'valueInMinorUnits' => [
                'type' => 'integer',
                'description' => esc_html__('Value in minor units (cents)', 'give'),
                'readonly' => true,
            ],
            'currency' => [
                'type' => 'string',
                'format' => 'text-field',
                'description' => esc_html__('Currency code in uppercase three letter format (e.g., USD, EUR)', 'give'),
            ],
        ]);

        return $moneySchemaType;
    }
}
