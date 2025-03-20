/**
 * @unreleased
 */
export default async function updateOrder(url: string, formData: FormData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    const responseJson = await response.json();

    if (!responseJson.success) {
        throw responseJson.data.error;
    }

    return responseJson.data.id;
}
