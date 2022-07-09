/**
 * Returns default values from an array of field collection objects
 *
 * @unreleased
 *
 * @param sections
 * @return {*}
 */
import {Section} from '@givewp/forms/types';
import {getGroupFields} from './groups';

export default function getDefaultValuesFromSections(sections: Section[]) {
    return sections.reduce((values, section) => {
        const fields = getGroupFields(section);

        fields.forEach((field) => (values[field.name] = field.defaultValue));

        return values;
    }, {});
}
