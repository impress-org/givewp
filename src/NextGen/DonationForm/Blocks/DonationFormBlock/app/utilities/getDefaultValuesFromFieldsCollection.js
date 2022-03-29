/**
 * Returns default values from an array of field collection objects
 *
 * @unreleased
 *
 * @param fields
 * @return {*}
 */
export default function getDefaultValuesFromFieldsCollection( fields ) {
    return fields.reduce((rules, field) => {
        if (field.nodes) {
            field.nodes.map(({name, defaultValue}) => {
                rules[name] = defaultValue;
            });
        } else {
            rules[field.name] = field?.defaultValue;
        }

        return rules;
    }, {});
}
