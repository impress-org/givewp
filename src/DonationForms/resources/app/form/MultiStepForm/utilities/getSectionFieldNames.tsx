import {Field, isField, Section} from '@givewp/forms/types';

/**
 * @unreleased
 */
export default function getSectionFieldNames(section: Section) {
    return section.reduceNodes(
        (names, field: Field) => {
            return names.concat(field.name);
        },
        [],
        isField
    );
}