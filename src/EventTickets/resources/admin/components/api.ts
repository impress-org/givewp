export default class EventTicketsApi {
    private readonly apiRoot: string;
    private readonly headers: {'X-WP-Nonce': string; 'Content-Type': string};

    constructor({apiNonce, apiRoot}) {
        this.apiRoot = apiRoot;
        this.headers = {
            'Content-Type': 'application/json',
            'X-WP-Nonce': apiNonce,
        };
    }

    fetchWithArgs = (endpoint, args, method = 'GET', signal = null) => {
        const url = new URL(this.apiRoot + endpoint);
        for (const [param, value] of Object.entries(args)) {
            value !== '' && url.searchParams.set(param, value as string);
        }
        return fetch(url.href, {
            method: method,
            signal: signal,
            headers: this.headers,
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error();
                }
                return res.text();
            })
            .then((text) => {
                try {
                    return text ? JSON.parse(text) : {};
                } catch (error) {
                    console.error('Failed to parse JSON:', error);
                    return {};
                }
            });
    };
}
