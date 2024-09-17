export async function FETCH_CAMPAIGN() {
    const response = await fetch('/wp-json/give/v2/get-campaign', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })

    return response.ok ? await response.json() : [];

}
