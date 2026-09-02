import apiFetch from '@wordpress/api-fetch';
import useSWR from 'swr';

const NAMESPACE = '/give-api/v2/migrations';

/**
 * @since 4.16.8 Replaced axios with @wordpress/api-fetch, which resolves with the parsed
 *            response body and supplies the REST root and nonce from WordPress core.
 */
const API = {
    get: (endpoint) => apiFetch({path: NAMESPACE + endpoint}),
    post: (endpoint, data) => apiFetch({path: NAMESPACE + endpoint, method: 'POST', data}),
    delete: (endpoint) => apiFetch({path: NAMESPACE + endpoint, method: 'DELETE'}),
};

export default API;

// SWR Fetcher
export const Fetcher = (endpoint) =>
    API.get(endpoint).then(({data, ...rest}) => {
        return {
            data,
            response: rest,
        };
    });

export const useMigrationFetcher = (endpoint, params = {}) => {
    const {data, error, mutate} = useSWR(endpoint, Fetcher, params);
    return {
        data: data ? data.data : undefined,
        isLoading: !error && !data,
        isError: error,
        response: data ? data.response : undefined,
        mutate,
    };
};

/**
 * GET endpoint with additional parameters.
 *
 * @since 4.16.8 apiFetch's root URL middleware rewrites the separator on sites without
 *            pretty permalinks, so the endpoint always uses '?' here.
 */
export const getEndpoint = (endpoint, data) => {
    if (data) {
        return endpoint + '?' + new URLSearchParams(data).toString();
    }

    return endpoint;
};
