<?php

namespace Give\API\REST\V3\Support;

/**
 * @unreleased
 */
class ValueObjectFormatter
{
    /**
     * @unreleased
     */
    public static function formatValueObjects(array $data, array $valueObjectFields = ['period', 'status', 'mode']): array
    {
        foreach ($valueObjectFields as $field) {
            if (isset($data[$field]) && is_object($data[$field]) && method_exists($data[$field], 'getValue')) {
                $data[$field] = $data[$field]->getValue();
            }
        }

        return $data;
    }
}
