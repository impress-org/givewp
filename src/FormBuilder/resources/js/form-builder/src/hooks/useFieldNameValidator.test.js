import {getFieldNameFrequency, getFieldNameSuggestion} from './useFieldNameValidator';

jest.mock('@givewp/form-builder/common', () => ({
    getWindowData: jest.fn().mockReturnValue({
        disallowedFieldNames: [],
    }),
}));

describe('useFieldNameValidator', () => {
    it('counts the frequency of a field name', () => {
        expect(getFieldNameFrequency('field-1', [])).toBe(0);
        expect(getFieldNameFrequency('field-1', ['field-1'])).toBe(1);
        expect(getFieldNameFrequency('field-1', ['field-1', 'field-1'])).toBe(2);
    });

    it('suggests a name to be unique', () => {
        expect(getFieldNameSuggestion('text-field', ['text-field'])).toBe('text-field_1');
        expect(getFieldNameSuggestion('text-field', ['text-field', 'text-field_1'])).toBe('text-field_2');
        expect(getFieldNameSuggestion('text-field', ['text-field', 'text-field_2'])).toBe('text-field_3');
    });
});
