import useSWR from 'swr';
import lagData from './hooks/lagData';
import apiFetch from '@wordpress/api-fetch';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

let controller = null;
const headers = {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveDonationForms.apiNonce,
};

export const fetchWithArgs = (endpoint, args, method = 'GET', signal = null) => {
    const url = new URL(window.GiveDonationForms.apiRoot + endpoint);
    url.searchParams.set('_wpnonce', window.GiveDonationForms.apiNonce);
    for (const [param, value] of Object.entries(args)) {
        url.searchParams.set(param, value as string);
    }
    console.log(apiFetch);
    return apiFetch({
        url: url.href,
        method: method,
        signal: signal,
        //headers: headers,
    })
        .then((res) => res)
        .catch((err) => {
            throw new Error()
        });
}

const Fetcher = (params) => {
    if(controller instanceof AbortController) controller.abort();
    controller = new AbortController();
    return fetchWithArgs('', params, 'GET', controller.signal);
}

// SWR Fetcher
export function useDonationForms({page, perPage, status, search}) {
    const {data, error, isValidating} = useSWR({page, perPage, status, search}, Fetcher, {
        use: [lagData],
        onErrorRetry: (error, key, config, revalidate, { retryCount }) => {
            //don't retry if we cancelled the initial request
            if(error.name == 'AbortError') return;
            if (retryCount >= 5) return;
            const retryAfter = (retryCount + 1) * 500;
            setTimeout(() => revalidate({ retryCount }), retryAfter);
        }
    });

    return {data, error, isValidating};
}
