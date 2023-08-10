/**
 * @0.6.0
 */
export default function convertValuesToFormData(values: object): FormData {
    const formData = new FormData();

    for (const key in values) {
        formData.append(key, values[key]);
    }

    return formData;
}
