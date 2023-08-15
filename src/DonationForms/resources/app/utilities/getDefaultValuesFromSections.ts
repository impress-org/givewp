/**
 * Returns default values from an array of field collection objects
 *
 * @since 0.1.0
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
            if (field.defaultValue !== null) {
                values[field.name] = field.defaultValue;
            }

            return values;
        },
        {}
    );
}
