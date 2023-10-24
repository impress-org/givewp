/**
 * @since 3.0.0
 */
export default function convertValuesToFormData(values: object): FormData {
    const formData = new FormData();
    for (const valueKey in values) {
        const value = values[valueKey];

        if (value !== null && typeof value === 'object' && !(value instanceof File)) {
            for (const objKey in value) {
                formData.append(`${valueKey}[${objKey}]`, value[objKey]);
            }
        } else {
            formData.append(valueKey, value);
        }
    }

    return formData;
}
