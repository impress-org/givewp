import useSWR from 'swr';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const headers = {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.GiveDonationForms.apiNonce,
};

export const fetchWithArgs = (endpoint, args, method = 'GET') => {
    console.log(endpoint);
    const url = new URL(window.GiveDonationForms.apiRoot + endpoint);
    for (const [param, value] of Object.entries(args)) {
        url.searchParams.set(param, value as string);
    }
    return fetch(url.href, {
        method: method,
        headers: headers,
    }).then(res => res.json());
}


const Fetcher = (params) => {
    return fetchWithArgs('', {
        page: params.split(',')[0],
        perPage: params.split(',')[1]
    });
}
// SWR Fetcher
export const useDonationForms = ({page, perPage}, swrParams = {}) => {
    return useSWR(keyFunction({page, perPage}), Fetcher, swrParams);
};

export const keyFunction = ({page, perPage}) => {
    return `${page},${perPage}`
}
