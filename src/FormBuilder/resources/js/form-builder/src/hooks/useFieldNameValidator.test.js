import {getFieldNameFrequency, getFieldNameSuggestion} from './useFieldNameValidator';

/**
 * We are checking for uniqueness after the field name is updated.
 * Therefor the field name will be in the list at least once.
 */

it('is counts the frequency of a field name', () => {
    expect(getFieldNameFrequency('field-1', [])).toBe(0);
    expect(getFieldNameFrequency('field-1', ['field-1'])).toBe(1);
    expect(getFieldNameFrequency('field-1', ['field-1', 'field-1'])).toBe(2);
});

it('suggests a name to be unique', () => {
    expect(getFieldNameSuggestion('text-field', ['text-field'])).toBe('text-field-1');

    expect(getFieldNameSuggestion('text-field', ['text-field', 'text-field-1'])).toBe('text-field-2');

    expect(getFieldNameSuggestion('text-field', ['text-field', 'text-field-2'])).toBe('text-field-3');
});
