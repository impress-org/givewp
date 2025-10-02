import {__, sprintf} from '@wordpress/i18n';
import {JSONSchemaType} from 'ajv';
import addErrors from 'ajv-errors';
import addFormats from 'ajv-formats';

/**
 * Create an AJV resolver for react-hook-form with WordPress REST API schema
 *
 * This function creates a custom resolver that works with WordPress REST API schemas (Draft 03/04)
 * and provides enhanced frontend validation using AJV (Draft 7/2019-09). It handles:
 * - Transforming WordPress Draft 03/04 schema to Draft 7/2019-09 for AJV compatibility
 * - Data transformation before validation (string numbers, enum handling, etc.)
 * - Error handling and fallback behavior
 *
 * Key advantage: WordPress REST API supports most JSON Schema Draft 4 features but lacks
 * some advanced features (if/then/else, allOf, not) that AJV can provide for enhanced frontend validation.
 *
 * @since 4.10.0 Refactor transformWordPressSchemaToDraft7 to handle readonly/readOnly fields and conditionally remove enum from nullable fields when value is null to prevent AJV conflicts
 * @since 4.9.0
 *
 * @param schema - The JSON Schema from WordPress REST API
 * @returns A resolver function compatible with react-hook-form
 */
export function ajvResolver(schema: JSONSchemaType<any>) {
    return (data: any) => {
        try {
            // Ensure we have valid data to validate
            if (!data || typeof data !== 'object') {
                return {values: data || {}, errors: {}};
            }

            const transformedData = transformFormDataForValidation(data, schema);
            const ajv = configureAjvForWordPress();
            const transformedSchema = transformWordPressSchemaToDraft7(schema, data);
            const validate = ajv.compile(transformedSchema);
            const valid = validate(transformedData);

            if (valid) {
                return {values: transformedData, errors: {}};
            } else {
                console.error('🔴 Validation failed, errors:', validate.errors);
                const errors: any = {};
                if (validate.errors) {
                    validate.errors.forEach((error) => {
                        const path = error.instancePath || error.schemaPath;
                        if (path) {
                            const fieldName = path.replace('/', '');
                            // Use the error message from ajv-errors
                            // ajv-errors should provide the custom message in error.message
                            const errorMessage = error.message || sprintf(__('%s is invalid.', 'give'), fieldName);

                            errors[fieldName] = {
                                type: 'validation',
                                message: errorMessage,
                            };
                        }
                    });
                }
                return {values: {}, errors};
            }
        } catch (error) {
            console.error('AJV validation error:', error);
            return {values: data, errors: {}};
        }
    };
}

/**
 * Configure standard AJV (Draft 7/2019-09) for WordPress REST API compatibility
 *
 * WordPress REST API Schema Characteristics (Draft 03/04):
 * - Uses 'required: true' on individual properties (Draft 03 syntax)
 * - Uses 'readonly' property (lowercase, WordPress REST API standard)
 * - Supports most JSON Schema Draft 4 features including:
 *   * anyOf, oneOf (since WordPress 5.6.0)
 *   * Basic validation: type, format, enum, pattern, constraints
 *   * Object/array validation: properties, items, additionalProperties
 * - Does NOT support: allOf, not, if/then/else (conditional validation)
 *
 * This function configures AJV (Draft 7/2019-09) to work with WordPress schemas:
 * - Transforms WordPress Draft 03/04 schemas to Draft 7/2019-09 syntax
 * - Converts 'required: true' on individual properties to 'required' array at object level
 * - Adds all standard JSON Schema formats using ajv-formats package
 * - Adds custom error messages using ajv-errors package
 * - Adds WordPress-specific custom formats (text-field, textarea-field)
 * - Disables schema validation to avoid conflicts with WordPress schema extensions
 * - Enables advanced validation features that WordPress ignores
 *
 * Validation Features Available:
 *
 * Backend (WordPress REST API) + Frontend (AJV):
 * - Type validation (string, integer, boolean, number, array, object, null)
 * - Required field validation
 * - Format validation (email, date-time, uri, ip, hex-color, uuid, text-field, textarea-field, and all standard formats via ajv-formats)
 * - Enum validation
 * - Constraint validation (minLength, maxLength, minimum, maximum, etc.)
 * - Pattern validation (regex)
 * - Array validation (items, minItems, maxItems, uniqueItems)
 * - Object validation (properties, additionalProperties, patternProperties)
 * - Schema composition: anyOf, oneOf (WordPress 5.6.0+)
 *
 * Frontend Only (AJV Draft 7/2019-09):
 * - Conditional validation (if/then/else)
 * - Advanced schema composition (allOf, not)
 * - Advanced references ($ref with complex paths)
 * - Dependent required fields
 * - Dynamic validation based on other field values
 * - More comprehensive format validation
 *
 * References:
 * - WordPress REST API Schema Documentation: https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/
 * - It only supports a subset of the draft-04 and draft-03 meta-schemas: https://developer.wordpress.org/news/2024/07/json-schema-in-wordpress/#wordpress-rest-api
 * - WordPress 5.6.0 anyOf/oneOf Support: https://core.trac.wordpress.org/ticket/51025
 * - Implementation Changeset: https://core.trac.wordpress.org/changeset/49246
 * - WordPress uses 'readonly' (lowercase) vs JSON Schema 'readOnly': https://core.trac.wordpress.org/ticket/56152
 * - WordPress 5.5.0 UUID format support: https://core.trac.wordpress.org/ticket/50053
 * - WordPress 5.9.0 text-field/textarea-field formats: https://core.trac.wordpress.org/changeset/49246
 * - rest_validate_value_from_schema(): https://developer.wordpress.org/reference/functions/rest_validate_value_from_schema/
 * - rest_get_allowed_schema_keywords(): https://developer.wordpress.org/reference/functions/rest_get_allowed_schema_keywords/
 *
 * @returns Configured AJV instance (Draft 7/2019-09) for WordPress compatibility
 */
function configureAjvForWordPress() {
    // Use standard AJV (Draft 7/2019-09) and transform WordPress schemas to be compatible
    const AjvClass = require('ajv').default || require('ajv');

    const ajv = new AjvClass({
        // Disable schema validation to avoid conflicts with WordPress schemas
        validateSchema: false,
        // Disable strict mode to allow WordPress extensions like 'readonly'
        strict: false,
        // Enable all errors for better debugging
        allErrors: true,
        verbose: true,
    });

    // Add all standard JSON Schema formats using ajv-formats
    addFormats(ajv);

    // Add custom error messages support using ajv-errors
    addErrors(ajv);

    // Add WordPress-specific custom formats that are not in the standard
    ajv.addFormat('text-field', true); // WordPress custom format - no validation, only sanitization
    ajv.addFormat('textarea-field', true); // WordPress custom format - no validation, only sanitization
    ajv.addFormat('integer', true); // WordPress custom format - no validation, only sanitization
    ajv.addFormat('boolean', true); // WordPress custom format - no validation, only sanitization

    // Transform WordPress schemas to be compatible with Draft 7/2019-09
    // This converts Draft 03/04 syntax (required: true on properties) to Draft 7 syntax
    const originalCompile = ajv.compile.bind(ajv);
    ajv.compile = function (schema: JSONSchemaType<any>) {
        try {
            const transformedSchema = transformWordPressSchemaToDraft7(schema);
            return originalCompile(transformedSchema);
        } catch (error) {
            console.error('Schema transformation error:', error);
            return originalCompile(schema);
        }
    };

    return ajv;
}

/**
 * Transform WordPress schema from Draft 03/04 syntax to Draft 7/2019-09 syntax
 *
 * This function converts WordPress REST API schemas (Draft 03/04) to be compatible
 * with AJV (Draft 7/2019-09). The transformation includes:
 * - Converts 'required: true' on individual properties to 'required' array at object level
 * - Updates $schema reference from Draft 04 to Draft 7/2019-09
 * - Removes readonly/readOnly fields from validation (they shouldn't be validated by frontend)
 * - Conditionally removes enum from nullable fields when value is null to prevent AJV conflicts
 * - Preserves all advanced features (if/then/else, allOf, etc.) that WordPress ignores
 *   but AJV can use for enhanced frontend validation
 *
 * Key benefit: WordPress schemas can include advanced validation rules (if/then/else, allOf, not)
 * that are ignored by the backend but utilized by the frontend for better UX.
 */
function transformWordPressSchemaToDraft7(schema: JSONSchemaType<any>, data?: any): JSONSchemaType<any> {
    if (!schema || typeof schema !== 'object') {
        return schema;
    }

    const transformed = JSON.parse(JSON.stringify(schema));

    // Update $schema reference to Draft 7/2019-09
    if (transformed.$schema) {
        transformed.$schema = 'https://json-schema.org/draft/2019-09/schema';
    }

    if (transformed.properties && typeof transformed.properties === 'object') {
        const requiredFields: string[] = [];
        const errorMessages: any = {};

        Object.keys(transformed.properties).forEach((key) => {
            const prop = transformed.properties[key];

            // Early return if prop is not a valid object
            if (!prop || typeof prop !== 'object') {
                return;
            }

            // Converts 'required: true' on individual properties to 'required' array at object level
            if (prop.required === true) {
                requiredFields.push(key);
                delete prop.required;
            }

            // Remove readonly/readOnly fields from validation (they shouldn't be validated by frontend)
            if (prop.readonly === true || prop.readOnly === true) {
                delete transformed.properties[key];
                return;
            }

            // For WordPress Array type + enum (like honorific), conditionally remove enum based on current value
            // This prevents AJV conflicts when nullable fields have null values
            if (Array.isArray(prop.type) && prop.enum) {
                const currentValue = data && data[key];
                const allowsNull = prop.type.includes('null');
                if (currentValue === null && allowsNull) {
                    delete prop.enum;
                }
            }

            // Add custom error messages for each property
            errorMessages[key] = getCustomErrorMessage(prop, key);
        });

        if (requiredFields.length > 0) {
            transformed.required = requiredFields;
        }

        // Add error messages to the schema
        if (Object.keys(errorMessages).length > 0) {
            transformed.errorMessage = {
                properties: errorMessages,
                required: __('Required fields are missing.', 'give'),
                _: __('Please check the form for errors.', 'give'),
            };
        }
    }

    return transformed;
}

/**
 * Generate custom error messages for schema properties using ajv-errors
 *
 * This function creates specific error messages for different validation types
 * based on the property schema, providing better user experience.
 *
 * @param prop - The property schema object
 * @param fieldName - The name of the field
 * @returns Custom error message string for the field
 */
function getCustomErrorMessage(prop: any, fieldName: string): string {
    // Priority order: format > type > constraints > generic

    // Format validation messages (highest priority)
    if (prop.format) {
        switch (prop.format) {
            case 'email':
                return sprintf(__('%s must be a valid email address.', 'give'), fieldName);
            case 'uri':
                return sprintf(__('%s must be a valid URL.', 'give'), fieldName);
            case 'date-time':
                return sprintf(__('%s must be a valid date and time.', 'give'), fieldName);
            case 'uuid':
                return sprintf(__('%s must be a valid UUID.', 'give'), fieldName);
            case 'hex-color':
                return sprintf(__('%s must be a valid color code.', 'give'), fieldName);
            default:
                return sprintf(__('%s format is invalid.', 'give'), fieldName);
        }
    }

    // Type validation messages
    if (prop.type) {
        if (prop.type === 'string') {
            return sprintf(__('%s must be text.', 'give'), fieldName);
        } else if (prop.type === 'number') {
            return sprintf(__('%s must be a number.', 'give'), fieldName);
        } else if (prop.type === 'integer') {
            return sprintf(__('%s must be a whole number.', 'give'), fieldName);
        } else if (prop.type === 'boolean') {
            return sprintf(__('%s must be true or false.', 'give'), fieldName);
        } else if (prop.type === 'array') {
            return sprintf(__('%s must be a list.', 'give'), fieldName);
        } else if (prop.type === 'object') {
            return sprintf(__('%s must be an object.', 'give'), fieldName);
        }
    }

    // Enum validation messages
    if (prop.enum && Array.isArray(prop.enum)) {
        return sprintf(__('%s must be one of: %s', 'give'), fieldName, prop.enum.join(', '));
    }

    // Constraint validation messages
    if (prop.minLength !== undefined) {
        return sprintf(__('%s must be at least %d characters long.', 'give'), fieldName, prop.minLength);
    }
    if (prop.maxLength !== undefined) {
        return sprintf(__('%s must be no more than %d characters long.', 'give'), fieldName, prop.maxLength);
    }
    if (prop.minimum !== undefined) {
        return sprintf(__('%s must be at least %s.', 'give'), fieldName, prop.minimum);
    }
    if (prop.maximum !== undefined) {
        return sprintf(__('%s must be no more than %s.', 'give'), fieldName, prop.maximum);
    }
    if (prop.pattern) {
        return sprintf(__('%s format is invalid.', 'give'), fieldName);
    }

    // Generic fallback
    return sprintf(__('%s is invalid.', 'give'), fieldName);
}

/**
 * Transform form data to be compatible with JSON Schema validation
 *
 * This function handles common form data issues that occur when HTML forms send
 * data that doesn't match the expected schema types:
 * - Converts string numbers to actual numbers based on schema type definitions
 * - Handles enum fields with null/empty string values (converts to null if schema allows)
 * - Removes non-required fields that are not present in form data
 * - Recursively processes nested objects and arrays
 *
 * This ensures that form data matches the schema expectations for validation,
 * working with both WordPress backend validation and AJV frontend validation.
 */
function transformFormDataForValidation(data: any, schema: JSONSchemaType<any>): any {
    if (!data || !schema || typeof data !== 'object') {
        return data || {};
    }
    const transformed = JSON.parse(JSON.stringify(data)); // Deep clone

    // Recursively transform nested objects
    function transformObject(obj: any, schemaObj: any): any {
        if (!obj || !schemaObj || typeof obj !== 'object') {
            return obj;
        }

        const result = {...obj};

        if (schemaObj.properties) {
            Object.keys(schemaObj.properties).forEach((key) => {
                const propSchema = schemaObj.properties[key];
                const value = result[key];

                // Skip validation for fields that are not present in form data and not required
                const isRequired = Array.isArray(schemaObj.required) && schemaObj.required.includes(key);
                const isFieldPresent = value !== undefined && value !== null;

                // Remove fields that are not present and not required from the result
                // Note: We allow empty strings ('') to be present so they can be saved to clear fields
                if (!isFieldPresent && !isRequired) {
                    delete result[key];
                    return; // Skip processing this field
                }

                // Only process fields that are present in the form OR are required
                if (propSchema && (isFieldPresent || isRequired)) {
                    // Handle number types
                    if (propSchema.type === 'number' && typeof value === 'string') {
                        // Convert string to number
                        const numValue = parseFloat(value);
                        if (!isNaN(numValue)) {
                            result[key] = numValue;
                        }
                    } else if (propSchema.type === 'integer' && typeof value === 'string') {
                        // Convert string to integer
                        const intValue = parseInt(value, 10);
                        if (!isNaN(intValue)) {
                            result[key] = intValue;
                        }
                    }
                    // Handle boolean types
                    else if (propSchema.type === 'boolean') {
                        if (typeof value === 'string') {
                            result[key] = value === 'true' || value === '1' || value === 'yes';
                        } else if (typeof value === 'number') {
                            result[key] = value === 1;
                        }
                    }
                    // Handle enum types - ensure value is valid
                    else if (propSchema.enum && Array.isArray(propSchema.enum)) {
                        // Check if null is allowed (when type includes 'null')
                        const allowsNull = Array.isArray(propSchema.type) && propSchema.type.includes('null');

                        // If value is null, undefined, or empty string and null is allowed, set to null
                        if ((value === null || value === undefined || value === '') && allowsNull) {
                            result[key] = null;
                        }
                        // If value is not in enum and not null/empty, try to find a close match
                        else if (!propSchema.enum.includes(value)) {
                            // If value is not in enum, try to find a close match or use first valid value
                            const stringValue = String(value).toLowerCase();
                            const validValue = propSchema.enum.find(
                                (enumValue) => String(enumValue).toLowerCase() === stringValue
                            );
                            if (validValue !== undefined) {
                                result[key] = validValue;
                            } else if (propSchema.enum.length > 0) {
                                // Use first enum value as fallback
                                result[key] = propSchema.enum[0];
                            }
                        }
                    }
                    // Handle oneOf schemas (like createdAt/updatedAt for donations)
                    else if (propSchema.oneOf && Array.isArray(propSchema.oneOf)) {
                        result[key] = transformOneOfValue(value, propSchema.oneOf);
                    }
                    // Handle array types with string/null (like createdAt/renewsAt for subscriptions)
                    else if (
                        Array.isArray(propSchema.type) &&
                        propSchema.type.includes('string') &&
                        propSchema.format === 'date-time'
                    ) {
                        // For subscription date fields that expect string or null
                        if (value === null || value === undefined) {
                            result[key] = null;
                        } else if (typeof value === 'string') {
                            // Handle ISO 8601 date strings (with or without timezone)
                            // Examples: '2025-07-15T16:34:57', '2025-07-15T16:34:57Z', '2025-07-15T16:34:57.000Z'
                            const date = new Date(value);
                            if (!isNaN(date.getTime())) {
                                // Ensure the string is in proper ISO format
                                const isoString = date.toISOString();
                                result[key] = isoString;
                            } else {
                                result[key] = null;
                            }
                        } else if (value instanceof Date) {
                            result[key] = value.toISOString();
                        } else {
                            result[key] = null;
                        }
                    }
                    // Handle nested objects
                    else if (
                        (propSchema.type === 'object' ||
                            (Array.isArray(propSchema.type) && propSchema.type.includes('object'))) &&
                        propSchema.properties
                    ) {
                        // Recursively transform nested objects
                        result[key] = transformObject(value, propSchema);
                    }
                }
            });
        }

        return result;
    }

    // Call transformObject to process the data
    return transformObject(transformed, schema);
}

/**
 * Transform values for oneOf schemas (like createdAt/updatedAt)
 */
function transformOneOfValue(value: any, oneOfSchemas: any[]): any {
    // If value is already null, return null
    if (value === null || value === undefined) {
        return null;
    }

    // Check each oneOf schema to see which one matches
    for (const schema of oneOfSchemas) {
        // If schema expects a string
        if (schema.type === 'string') {
            if (typeof value === 'string') {
                // Check if it's a valid ISO date-time string
                const date = new Date(value);
                if (!isNaN(date.getTime())) {
                    return value; // Return as string if valid
                }
            }
        }
        // If schema expects an object with date property (like createdAt/updatedAt)
        else if (schema.type === 'object' && schema.properties && schema.properties.date) {
            // If value is already an object with date property, validate and return
            if (typeof value === 'object' && value.date) {
                const date = new Date(value.date);
                if (!isNaN(date.getTime())) {
                    // Convert to ISO 8601 format if needed
                    const isoDate = date.toISOString();
                    return {
                        date: isoDate,
                        timezone: value.timezone || 'UTC',
                        timezone_type: value.timezone_type || 3,
                    };
                }
            }
            // If value is a string, convert to object format
            else if (typeof value === 'string') {
                const date = new Date(value);
                if (!isNaN(date.getTime())) {
                    return {
                        date: date.toISOString(),
                        timezone: 'UTC',
                        timezone_type: 3,
                    };
                }
            }
        }
        // If schema expects null
        else if (schema.type === 'null') {
            if (value === null) {
                return null;
            }
        }
    }

    // If value is a Date object, convert to object format
    if (value instanceof Date) {
        return {
            date: value.toISOString(),
            timezone: 'UTC',
            timezone_type: 3,
        };
    }

    // If value is a string that looks like a date, convert to object format
    if (typeof value === 'string') {
        const date = new Date(value);
        if (!isNaN(date.getTime())) {
            return {
                date: value,
                timezone: 'UTC',
                timezone_type: 3,
            };
        }
    }

    // Fallback: return null
    return null;
}
