import apiFetch from '@wordpress/api-fetch';
import {__, sprintf} from '@wordpress/i18n';
import {JSONSchemaType} from 'ajv';

/**
 * Fetch schema from WordPress REST API and configure AJV resolver
 *
 * This function encapsulates the common pattern of fetching a schema from the WordPress REST API
 * and setting up a custom AJV resolver for form validation. It handles:
 * - Fetching the schema using OPTIONS method
 * - Creating a custom resolver with AJV validation
 * - Data transformation before validation
 * - Error handling and fallback resolvers
 *
 * @unreleased
 *
 * @param path - The REST API path to fetch the schema from (e.g., '/givewp/v3/campaigns/123')
 * @param setResolver - Function to set the resolver (from useForm)
 * @returns Promise that resolves when schema is loaded and resolver is configured
 */
export async function fetchSchemaAndConfigureResolver(
    path: string,
    setResolver: (resolver: any) => void
): Promise<void> {
    try {
        const {schema}: {schema: JSONSchemaType<any>} = await apiFetch({
            path,
            method: 'OPTIONS',
        });

        const customResolver = (data: any) => {
            try {
                // Ensure we have valid data to validate
                if (!data || typeof data !== 'object') {
                    return {values: data || {}, errors: {}};
                }

                const transformedData = transformFormDataForValidation(data, schema);

                const ajv = configureAjvWithDraft04Support();
                const validate = ajv.compile(schema);
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
                                const errorMessage = getTranslatedErrorMessage(error, fieldName);
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

        setResolver({
            resolver: customResolver,
        });
    } catch (error) {
        console.error('Failed to load schema:', error);
        setResolver({
            resolver: (data: any) => ({values: data, errors: {}}),
        });
    }
}

/**
 * Configure AJV with WordPress REST API Draft 04 support
 *
 * WordPress REST API Schema Extensions:
 * - Uses 'required: true' on individual properties (Draft 03 syntax)
 * - Uses 'readonly' property (lowercase, WordPress REST API standard)
 * - Supports other WordPress-specific schema properties
 *
 * This function uses the official ajv-draft-04 package which provides AJV with
 * built-in Draft 04 support. To handle WordPress's Draft 03 syntax, we:
 * - Use ajv-draft-04 for proper Draft 04 meta-schema support
 * - Transform WordPress schemas from Draft 03 to Draft 04 syntax before compilation
 * - Convert 'required: true' on individual properties to 'required' array at object level
 * - Add support for WordPress-specific and standard JSON Schema formats
 * - Maintain full data validation functionality with proper Draft 04 compliance
 *
 * This approach allows us to use the official Draft 04 support while accommodating
 * WordPress's hybrid Draft 03/04 schema format and custom formats.
 *
 * Validation still works for:
 * - Type validation (string, integer, boolean, etc.)
 * - Required field validation
 * - Format validation (email, date-time, etc.)
 * - Enum validation
 * - Constraint validation (minLength, maxLength, minimum, maximum, etc.)
 *
 * References:
 * - https://developer.wordpress.org/news/2024/07/json-schema-in-wordpress/#wordpress-rest-api
 * - https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#json-schema-basics
 *
 * @returns Configured AJV instance with WordPress Draft 04 support
 */
function configureAjvWithDraft04Support() {
    const AjvClass = require('ajv-draft-04').default || require('ajv-draft-04');

    const ajv = new AjvClass({
        // Enable schema validation - ajv-draft-04 should handle Draft 04 correctly
        validateSchema: true,
        // Disable strict mode to allow WordPress extensions like 'readonly'
        strict: false,
        // Enable all errors for better debugging
        allErrors: true,
        verbose: true,
    });

    // Add WordPress-specific and standard JSON Schema formats
    // WordPress custom format - always valid
    ajv.addFormat('text-field', true);

    // Standard JSON Schema formats
    ajv.addFormat('uri', (uri: string) => {
        // Validate URI format (more comprehensive than just HTTP/HTTPS)
        try {
            new URL(uri);
            return true;
        } catch {
            return false;
        }
    });

    ajv.addFormat('date-time', (dateTime: string) => {
        // Validate ISO 8601 date-time format (RFC 3339)
        const date = new Date(dateTime);
        return !isNaN(date.getTime()) && dateTime.includes('T');
    });

    ajv.addFormat('date', (date: string) => {
        // Validate ISO 8601 date format (YYYY-MM-DD)
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(date)) return false;
        const parsedDate = new Date(date);
        return !isNaN(parsedDate.getTime());
    });

    ajv.addFormat('time', (time: string) => {
        // Validate ISO 8601 time format (HH:MM:SS)
        const timeRegex = /^\d{2}:\d{2}:\d{2}(\.\d{3})?([+-]\d{2}:\d{2}|Z)?$/;
        return timeRegex.test(time);
    });

    ajv.addFormat('email', (email: string) => {
        // Validate email format using a comprehensive regex
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    });

    ajv.addFormat('hostname', (hostname: string) => {
        // Validate hostname format
        const hostnameRegex =
            /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        return hostnameRegex.test(hostname) && hostname.length <= 253;
    });

    ajv.addFormat('ipv4', (ip: string) => {
        // Validate IPv4 address
        const ipv4Regex = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipv4Regex.test(ip);
    });

    ajv.addFormat('ipv6', (ip: string) => {
        // Validate IPv6 address (simplified validation)
        const ipv6Regex = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$|^::1$|^::$/;
        return ipv6Regex.test(ip);
    });

    // Transform WordPress schemas to be compatible with Draft 04
    // This converts Draft 03 syntax (required: true on properties) to Draft 04 syntax
    const originalCompile = ajv.compile.bind(ajv);
    ajv.compile = function (schema: JSONSchemaType<any>) {
        try {
            const transformedSchema = transformWordPressSchemaToDraft04(schema);
            return originalCompile(transformedSchema);
        } catch (error) {
            console.error('Schema transformation error:', error);
            return originalCompile(schema);
        }
    };

    return ajv;
}

/**
 * Transform WordPress schema from Draft 03 syntax to Draft 04 syntax
 * Converts 'required: true' on individual properties to 'required' array at object level
 */
function transformWordPressSchemaToDraft04(schema: JSONSchemaType<any>): JSONSchemaType<any> {
    if (!schema || typeof schema !== 'object') {
        return schema;
    }

    const transformed = JSON.parse(JSON.stringify(schema));

    if (transformed.properties && typeof transformed.properties === 'object') {
        const requiredFields: string[] = [];

        Object.keys(transformed.properties).forEach((key) => {
            const prop = transformed.properties[key];
            if (prop && typeof prop === 'object' && prop.required === true) {
                requiredFields.push(key);
                delete prop.required;
            }
        });

        if (requiredFields.length > 0) {
            transformed.required = requiredFields;
        }
    }

    return transformed;
}

/**
 * Transform form data to convert string numbers to actual numbers based on schema
 * This handles the common issue where HTML forms send numeric values as strings
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
                const isFieldPresent = value !== undefined && value !== null && value !== '';

                // Remove fields that are not present and not required from the result
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
                            const date = new Date(value);
                            if (!isNaN(date.getTime())) {
                                result[key] = value; // Keep as string
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

/**
 * Generate translated error messages for validation errors
 *
 * @param error - AJV validation error object
 * @param fieldName - Name of the field that failed validation
 * @returns Translated error message
 */
function getTranslatedErrorMessage(error: any, fieldName: string): string {
    const {keyword, params} = error;

    // Generic error messages based on keyword
    const genericMessages: Record<string, string> = {
        required: __('This field is required.', 'give'),
        type: __('Please enter a valid value.', 'give'),
        format: __('Please enter a valid format.', 'give'),
        enum: __('Please select a valid option.', 'give'),
        minimum: __('Value must be at least %s.', 'give'),
        maximum: __('Value must be at most %s.', 'give'),
        minLength: __('Must be at least %s characters.', 'give'),
        maxLength: __('Must be at most %s characters.', 'give'),
        pattern: __('Please enter a valid format.', 'give'),
    };

    let genericMessage = genericMessages[keyword] || __('Please enter a valid value.', 'give');

    // Replace placeholders with actual values using sprintf
    if (params) {
        // Map AJV params to sprintf format
        const paramMap: Record<string, string> = {
            minimum: 'minimum',
            maximum: 'maximum',
            minLength: 'minLength',
            maxLength: 'maxLength',
        };

        const paramKey = paramMap[keyword];
        if (paramKey && params[paramKey] !== undefined) {
            genericMessage = sprintf(genericMessage, params[paramKey]);
        }
    }

    return genericMessage;
}
