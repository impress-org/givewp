/**
 * Returns default values from an array of field collection objects
 *
 * @param fields
 * @return {*}
 */
export default function getDefaultValuesFromFieldsCollection( fields ) {
	return fields.reduce((rules, field) => {
		rules[field.name] = field?.defaultValue;
		return rules;
	}, {});
}
