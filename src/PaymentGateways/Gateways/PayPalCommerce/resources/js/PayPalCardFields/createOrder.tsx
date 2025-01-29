// TODO: replace with GiveWP endpoint for creating order
export default async function createOrder(url: string, formData: FormData) {
    try {
        console.log('createOrder');
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const responseJson = await response.json();

        if (!responseJson.success) {
            throw responseJson.data.error;
        }

        return responseJson.data.id;
    } catch (err) {
        console.error(err);
    }
}
