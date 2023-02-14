import useSWR from 'swr';
import lagData from './hooks/lagData';
import useFallbackAsInitial from '@givewp/components/ListTable/hooks/useFallbackAsInitial';

export default class ListTableApi {
    private readonly apiRoot: string;
    private controller: AbortController | null;
    private readonly headers: {'X-WP-Nonce': string; 'Content-Type': string};
    private readonly swrOptions;

    constructor({apiNonce, apiRoot, preload = null}) {
        this.controller = null;
        this.apiRoot = apiRoot;
        this.headers = {
            'Content-Type': 'application/json',
            'X-WP-Nonce': apiNonce,
        };
        this.swrOptions = {
            use: [lagData],
            onErrorRetry: (error, key, config, revalidate, {retryCount}) => {
                //don't retry if we cancelled the initial request
                if (error.name == 'AbortError') return;
                if (retryCount >= 5) return;
                const retryAfter = (retryCount + 1) * 500;
                setTimeout(() => revalidate({retryCount}), retryAfter);
            },
        };
        if (preload) {
            this.swrOptions.fallbackData = preload;
            this.swrOptions.use.push(useFallbackAsInitial);
        }
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
                throw new Error();
            }
            return res.json();
        });
    };

    fetcher = (params) => {
        if (this.controller instanceof AbortController) this.controller.abort();
        this.controller = new AbortController();
        return this.fetchWithArgs('', params, 'GET', this.controller.signal);
    };

    // SWR Fetcher
    useListTable = ({page, perPage, sortColumn, sortDirection, locale, testMode, ...filters}) => {
        const {data, error, mutate, isValidating} = useSWR(
            {
                page,
                perPage,
                sortColumn,
                sortDirection,
                locale,
                testMode,
                ...filters,
            },
            this.fetcher,
            this.swrOptions
        );
        return {data, error, mutate, isValidating};
    };
}
