<?php

namespace Give\Tests\Unit\API\REST\V3;

/**
 * @since 4.9.0
 */
trait SchemaValidationTrait
{
    /**
     * @since 4.9.0
     */
    private function validateSchemaProperties($schema, $actualData)
    {
        if (!isset($schema['schema']['properties'])) {
            $this->fail('Schema does not contain properties');
        }

        $schemaProperties = $schema['schema']['properties'];
        $requiredProperties = $schema['schema']['required'] ?? [];

        // Check that all required properties exist
        foreach ($requiredProperties as $requiredProperty) {
            $this->assertArrayHasKey(
                $requiredProperty,
                $actualData,
                "Required property '{$requiredProperty}' is missing from response"
            );
        }

        // Check that all schema properties that exist in actual data are properly defined
        foreach ($actualData as $property => $value) {
            if (isset($schemaProperties[$property])) {
                // Property exists in schema, which is good
                continue;
            }

            // Allow some dynamic properties that might not be in schema
            $allowedDynamicProperties = ['_links', 'customFields'];
            if (!in_array($property, $allowedDynamicProperties)) {
                $this->fail("Property '{$property}' exists in response but not in schema");
            }
        }
    }

    /**
     * @since 4.9.0
     */
    private function validateDataTypes($schema, $actualData)
    {
        $schemaProperties = $schema['schema']['properties'];

        foreach ($actualData as $property => $value) {
            if (!isset($schemaProperties[$property])) {
                continue; // Skip properties not in schema
            }

            // Skip properties that don't have a type (like objects with properties)
            if (!isset($schemaProperties[$property]['type'])) {
                continue;
            }

            $expectedType = $schemaProperties[$property]['type'];
            $actualType = $this->getActualType($value);

            // Handle oneOf schemas
            if (isset($schemaProperties[$property]['oneOf'])) {
                $this->validateOneOfSchema($schemaProperties[$property]['oneOf'], $value, $property);
                continue;
            }

            // Handle array types
            if (is_array($expectedType)) {
                $this->assertContains(
                    $actualType,
                    $expectedType,
                    "Property '{$property}' type '{$actualType}' should be one of: " . implode(', ', $expectedType)
                );
            } else {
                $this->assertEquals(
                    $expectedType,
                    $actualType,
                    "Property '{$property}' type mismatch. Expected: {$expectedType}, Actual: {$actualType}"
                );
            }
        }
    }

    /**
     * @since 4.9.0
     */
    private function validateEnumValues($schema, $actualData)
    {
        $schemaProperties = $schema['schema']['properties'];

        foreach ($actualData as $property => $value) {
            if (!isset($schemaProperties[$property]['enum'])) {
                continue;
            }

            $allowedValues = $schemaProperties[$property]['enum'];
            $this->assertContains(
                $value,
                $allowedValues,
                "Property '{$property}' value '{$value}' is not in allowed enum values: " . implode(', ', $allowedValues)
            );
        }
    }

    /**
     * @since 4.9.0
     */
    private function validateOneOfSchema($oneOfSchemas, $value, $propertyName)
    {
        $validTypes = [];
        foreach ($oneOfSchemas as $schema) {
            $validTypes[] = $schema['type'];
        }

        $actualType = $this->getActualType($value);
        $this->assertContains(
            $actualType,
            $validTypes,
            "Property '{$propertyName}' type '{$actualType}' should be one of: " . implode(', ', $validTypes)
        );
    }

    /**
     * @since 4.9.0
     */
    private function validateDateFormat($dateValue, $propertyName)
    {
        if (is_string($dateValue)) {
            // Should be WordPress-compatible ISO 8601 format (without timezone)
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/',
                $dateValue,
                "Property '{$propertyName}' should be in WordPress-compatible ISO 8601 format (without timezone)"
            );
        } elseif (is_null($dateValue)) {
            // Null values are allowed for optional date fields
            return;
        } else {
            $this->fail("Property '{$propertyName}' should be a string in WordPress-compatible ISO 8601 format or null, got " . gettype($dateValue));
        }
    }

    /**
     * @since 4.9.0
     */
    private function getActualType($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'number';
        }
        if (is_string($value)) {
            return 'string';
        }
        if (is_array($value)) {
            // In JSON Schema, associative arrays are objects, indexed arrays are arrays
            return array_keys($value) === range(0, count($value) - 1) ? 'array' : 'object';
        }
        if (is_object($value)) {
            return 'object';
        }

        return gettype($value);
    }
}
