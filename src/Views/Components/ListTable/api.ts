/**
 * Calls a list endpoint's actions (`/trash`, `/delete`, `/setStatus`, …) with the nonce of the screen's
 * API settings. The list itself is fetched by `useListTableQuery`.
 *
 * @since TBD Keep only the request helper; the list engine fetches rows with SWR and `apiFetch`.
 */
export default class ListTableApi {
    private readonly apiRoot: string;
    private readonly headers: {'X-WP-Nonce': string; 'Content-Type': string};

    constructor({apiNonce, apiRoot}: {apiNonce: string; apiRoot: string; [key: string]: unknown}) {
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
        }).then((res) => {
            if (!res.ok) {
                return res.text().then((errorMessage) => {
                    throw new Error(errorMessage);
                });
            }
            return res.json();
        });
    };
}
