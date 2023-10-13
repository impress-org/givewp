/**
 * Returns default values from an array of field collection objects
 *
 * @since 3.0.0
 *
 * @param sections
 * @return {*}
 */
import {Section} from '@givewp/forms/types';
import {reduceFields} from './groups';

export default function getDefaultValuesFromSections(sections: Section[]) {
    return reduceFields(
        sections,
        (values, field) => {
            const defaultValue = field.defaultValue;

            if (defaultValue !== null && defaultValue !== '') {
                values[field.name] = defaultValue;
            }

            return values;
        },
        {}
    );
}
