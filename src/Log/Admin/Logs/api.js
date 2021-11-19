import axios from 'axios';
import useSWR from 'swr';

const API = axios.create({
    baseURL: window.GiveLogs.apiRoot,
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveLogs.apiNonce,
    },
});

export default API;

export const CancelToken = axios.CancelToken.source();

// SWR Fetcher
export const Fetcher = (endpoint) =>
    API.get(endpoint).then((res) => {
        const {data, ...rest} = res.data;
        return {
            data,
            response: rest,
        };
    });

export const useLogFetcher = (endpoint, params = {}) => {
    const {data, error} = useSWR(endpoint, Fetcher, params);
    return {
        data: data ? data.data : undefined,
        isLoading: !error && !data,
        isError: error,
        response: data ? data.response : undefined,
    };
};

// GET endpoint with additional parameters
export const getEndpoint = (endpoint, data) => {
    if (data) {
        const queryString = new URLSearchParams(data);
        // pretty url?
        const separator = window.GiveLogs.apiRoot.indexOf('?') === -1 ? '?' : '&';

        return endpoint + separator + queryString.toString();
    }

    return endpoint;
};
